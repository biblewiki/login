<?php


class PasswordLogin {

    /**
     * Login überprüfen
     * @param Db $db
     * @param array $formPacket
     * @return array
     */
    public static function checkLogin(Db $db, array $formPacket): array {

        $return = [];
        $return['success'] = false;

        // Abfrage vorbereiten
        $st = new SqlSelector('user');
        $st->addSelectElement('user.userId');
        $st->addSelectElement('role.roleType');
        $st->addSelectElement('user.state');
        $st->addSelectElement('user.emailState');
        $st->addSelectElement('user.password');
        $st->addSelectElement('user.passwordState');

        $st->addFromElement('INNER JOIN role ON role.roleId = user.roleId');

        $st->addWhereElement('username = :username');

        $st->addParam(':username', $formPacket['username'], \PDO::PARAM_STR);
        $row = $st->execute($db, false);
        unset ($st);

        // Überprüfen ob der Benutzername existiert
        if ($row) {

            // Überprüfen ob das Passwort übereinstimmt
            if (password_verify($formPacket['password'], $row['password'])) {

                // Überprüfen ob User nicht gesperrt ist
                if ($row['state'] !== 90) {

                    // Überprüfen ob Email bestätigt ist
                    if ($row['state'] > 10 && $row['emailState'] > 10) {
                        $return['success'] = true;
                        $return['passwordState'] = $row['passwordState'];
                        $return['userId'] = $row['userId'];
                        $return['roleType'] = $row['roleType'];

                        if ($row['state'] === 20) {
                            self::setUserState($db, $row['userId'], 30);
                        } elseif ($row['state'] === 30) {
                            self::setUserState($db, $row['userId'], 40);
                        }
                    } else {
                        $return['errorMsg'] = 'Ihre Email Adresse wurde noch nicht bestätigt.';
                    }
                } else {
                    $return['errorMsg'] = 'Ihr Benutzer ist gesperrt. Wenden Sie sich an einen Administrator';
                }
            } else {
                $return['warnMsg'] = 'Benutzername oder Passwort ist falsch';
            }
        } else {
            $return['warnMsg'] = 'Benutzername oder Passwort ist falsch';
        }

        return $return;
    }


    /**
     * Passwort reset Token überprüfen
     * @param Db $db
     * @param array $formPacket
     * @return array
     */
    public static function checkResetPasswordToken(Db $db, array $formPacket): array {

        $return = [];

        // Abfrage vorbereiten
        $st = new SqlSelector('user');
        $st->addSelectElement('user.passwordState');

        $st->addWhereElement('passwordToken = :passwordToken AND userId = :userId');

        $st->addParam(':passwordToken', $formPacket['passwordToken'], \PDO::PARAM_STR);
        $st->addParam(':userId', $formPacket['userId'], \PDO::PARAM_INT);
        $row = $st->execute($db, false);
        unset ($st);

        // Überprüfen ob der Token existiert
        if ($row) {

            // Überprüfen ob der Passwort Status stimmt
            if ($row['passwordState'] === 30) {
                $return['success'] = true;
            } else {
                $return['success'] = false;
                $return['errorMsg'] = 'Passwort kann nicht zurückgesetzt werden.';
            }
        } else {
            $return['success'] = false;
            $return['warnMsg'] = 'Token falsch';
        }

        return $return;
    }


    /**
     * Überprüft den Email Confirm Token
     * @param Db $db
     * @param array $data
     * @return array
     */
    public static function confirmEmail(Db $db, array $data): array {
        // Abfrage vorbereiten
        $st = new SqlSelector('user');
        $st->addSelectElement('user.emailState');
        $st->addSelectElement('user.emailToken');

        $st->addWhereElement('userId = :userId AND emailToken = :emailToken');

        $st->addParam(':userId', $data['userId'], \PDO::PARAM_INT);
        $st->addParam(':emailToken', $data['emailToken'], \PDO::PARAM_STR);
        $row = $st->execute($db, false);
        unset ($st);

        $return = [];
        $return['success'] = false;

        if ($row) {

            // Überprüfen ob der Emailstatus stimmt
            if ($row['emailState'] === 10) {

                // Überprüfen ob der Token valid ist
                if ($row['emailToken'] === $data['emailToken']) {
                    $formPacket = [];
                    $formPacket['oldVal_userId'] = $data['userId'];
                    $formPacket['state'] = 20;
                    $formPacket['emailState'] = 40;
                    $formPacket['emailToken'] = null;
                    $formPacket['openTS'] = date('Y-m-d H:i:s');

                    $save = new SaveData($db, 1, 'user');
                    $save->save($formPacket);
                    unset ($save);

                    $return['success'] = true;
                    $return['infoMsg'] = 'Bestätigung erfolgreich. Sie können sich nun anmelden';

                } else {
                    $return['errorMsg'] = 'Bestätigung fehlgeschlagen. Falscher Token';
                }
            } elseif ($row['emailState'] > 10) {
                $return['infoMsg'] = 'Ihre Email Adresse wurde bereits bestätigt';
            } else {
                $return['errorMsg'] = 'Bestätigung fehlgeschlagen. Melden Sie sich beim Administrator';
            }
        } else {
            $return['errorMsg'] = 'Bestätigung fehlgeschlagen. Melden Sie sich beim Administrator';
        }

        return $return;
    }


    /**
     * Registrieren
     * @param Db $db
     * @param array $formPacket
     * @return array
     */
    public static function register(Db $db, array $formPacket): array {

        $return = [];
        $return['success'] = false;

        // Überprüfen ob die Passwörter übereinstimmen
        if ($formPacket['password'] === $formPacket['passwordRepeat']) {

            $password = $formPacket['password'];

            // Passwortstärke ermitteln
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
            $specialChars = preg_match('@[^\w]@', $password);

            // Überprüfen ob das Passwort sicher ist
            if ($uppercase && $lowercase && $number && $specialChars && strlen($password) > 8) {

                // Überprüfen ob Email oder Benutzernameschon registriert
                $st = new SqlSelector('user');
                $st->addSelectElement('username');
                $st->addSelectElement('email');
                $st->addWhereElement('email = :email OR username = :username');
                $st->addParam(':username', $formPacket['username']);
                $st->addParam(':email', $formPacket['email']);
                $row = $st->execute($db);
                unset ($st);

                // Überprüfen ob Email schon existiert
                if ($row[0]['username'] === $formPacket['username']) {
                    $return['errorMsg'] = 'Username existiert bereits';

                // Überprüfen ob Email schon existiert
                } elseif ($row[0]['email'] === $formPacket['email']) {
                    $return['errorMsg'] = 'Emailadresse ist bereits registriert';

                // Neuer User hinzufügen
                } else {

                    $token = bin2hex(random_bytes(64));

                    $formPacket['password'] = password_hash($formPacket['password'], PASSWORD_DEFAULT);
                    $formPacket['emailDate'] = date('Y-m-d H:i:s');
                    $formPacket['emailToken'] = $token;

                    $save = new SaveData($db, 1, 'user');
                    $save->save($formPacket);
                    $userId = $save->getPrimaryKey()->value;
                    unset ($save);

                    $return['success'] = !!$userId;
                    $return['userId'] = $userId;
                    $return['name'] = $formPacket['firstName'] . ' ' . $formPacket['lastName'];
                    $return['email'] = $formPacket['email'];
                    $return['emailToken'] = $formPacket['emailToken'];
                    $return['infoMsg'] = 'Registrierung erfolgreich';
                }
            } else {
                $return['errorMsg'] = 'Passwort muss mindestens 8 Zeichen lang sein, mindestens ein Grossbuchstabe, eine Zahl und ein Sonderzeichen enthalten.';
            }
        } else {
            $return['errorMsg'] = 'Passwörter stimmen nicht überein';
        }

        return $return;
    }


    /**
     * User Pa<sswort zurücksetzen
     * @param Db $db
     * @param array $formPacket
     * @return array
     */
    public static function resetUserPassword(Db $db, array $formPacket): array {

        $return = [];
        $return['success'] = false;

        $tokenValidation = self::checkResetPasswordToken($db, $formPacket);

        // Überprüfen ob der Token valid ist
        if ($tokenValidation['success']) {

            // Überprüfen ob die Passwörter übereinstimmen
            if ($formPacket['password'] === $formPacket['passwordRepeat']) {

                $password = $formPacket['password'];

                // Passwortstärke ermitteln
                $uppercase = preg_match('@[A-Z]@', $password);
                $lowercase = preg_match('@[a-z]@', $password);
                $number    = preg_match('@[0-9]@', $password);
                $specialChars = preg_match('@[^\w]@', $password);

                // Überprüfen ob das Passwort sicher ist
                if ($uppercase && $lowercase && $number && $specialChars && strlen($password) > 8) {

                    $formPacket['oldVal_userId'] = $formPacket['userId'];
                    $formPacket['password'] = password_hash($formPacket['password'], PASSWORD_DEFAULT);
                    $formPacket['passwordState'] = 50;
                    $formPacket['passwordToken'] = null;
                    $formPacket['emailDate'] = date('Y-m-d H:i:s');
                    $formPacket['openTS'] = date('Y-m-d H:i:s');

                    $save = new SaveData($db, 1, 'user');
                    $save->save($formPacket);
                    $userId = $save->getPrimaryKey()->value;
                    unset ($save);

                    $return['success'] = !!$userId;
                    $return['infoMsg'] = 'Passwort wurde erfolgreich zurückgesetzt.';
                } else {
                    $return['errorMsg'] = 'Passwort muss mindestens 8 Zeichen lang sein, mindestens ein Grossbuchstabe, eine Zahl und ein Sonderzeichen enthalten.';
                }
            } else {
                $return['errorMsg'] = 'Passwörter stimmen nicht überein';
            }
        } else {
            $return = $tokenValidation;
        }

        return $return;
    }


    /**
     * Sendet das Mail für das Passwort zurückzusetzen
     * @param Db $db
     * @param array $formPacket
     * @param array $config
     * @return array
     */
    public static function sendPasswortResetMail(Db $db, array $formPacket, array $config): array {

        require_once 'Mail.php';

        $return = [];
        $return['success'] = false;

        // Überprüfen ob Email oder Benutzernameschon registriert
        $st = new SqlSelector('user');
        $st->addSelectElement('userId');
        $st->addSelectElement('firstName');
        $st->addSelectElement('lastName');
        $st->addSelectElement('email');
        $st->addSelectElement('passwordState');
        $st->addWhereElement('username = :username');
        $st->addParam(':username', $formPacket['username']);
        $row = $st->execute($db, false);
        unset ($st);

        if ($row) {

            if ($row['passwordState'] !== 60) {

                $token = bin2hex(random_bytes(64));

                // Token in DB speichern
                $formPacket['oldVal_userId'] = $row['userId'];
                $formPacket['passwordToken'] = $token;
                $formPacket['openTS'] = date('Y-m-d H:i:s');

                $save = new SaveData($db, 1, 'user');
                $save->save($formPacket);
                $id = $save->getPrimaryKey()->value;
                unset ($save);

                if ($id) {

                    $link = '';
                    $link .= $_SERVER['HTTPS'] ? 'https://' : 'http://';
                    $link .= $_SERVER['HTTP_HOST'];
                    $link .= '?bot=passwordLink&userId=' . $row['userId'] . '&passwordToken=' . $token;

                    $mail = new Mail($config['mail']);

                    $mail->to($row['email'], $row['firstName'] . ' ' . $row['lastName']);

                    $mail->subject('Passwort zurücksetzen');

                    $mail->html('Passwort zurücksetzen: ' . $link);

                    if ($mail->send()) {
                        $return['success'] = true;
                        $return['infoMsg'] = 'Wenn der Benutzername existiert, wurde eine Email mit dem Link für den Passwort-Reset verschickt.';

                    } else {
                        $return['errorMsg'] = 'Mail Fehler: ' . $mail->errorInfo();
                    }
                } else {
                    $return['errorMsg'] = 'Token speichern fehlgeschlagen';
                }
            } else {
                $return['errorMsg'] = 'Passwort kann nicht zurückgesetzt werden';
            }
        } else {
            $return['success'] = false;
            $return['infoMsg'] = 'Wenn der Benutzername existiert, wurde eine Email mit dem Link für den Passwort-Reset verschickt.';
        }

        return $return;
    }


    /**
     * Sendet das Mail um die Emailadresse zu bestätigen
     * @param array $data
     * @param array $config
     * @return array
     */
    public static function sendRegisterEmail(array $data, array $config): array {
        require_once 'Mail.php';

        $link = '';
        $link .= $_SERVER['HTTPS'] ? 'https://' : 'http://';
        $link .= $_SERVER['HTTP_HOST'];
        $link .= '?bot=emailLink&userId=' . $data['userId'] . '&emailToken=' . $data['emailToken'];

        $mail = new Mail($config['mail']);

        $mail->to($data['email'], $data['name']);

        $mail->subject('Emailadresse bestätigen');

        $mail->html('Emailadresse bestätigen: ' . $link);

        if ($mail->send()) {
            $return['success'] = true;
            $return['infoMsg'] = 'Bitte bestätigen Sie Ihre Mailadresse. Wir haben Ihnen dazu eine Email geschickt.';

        } else {
            $return['success'] = false;
            $return['errorMsg'] = 'Mail Fehler: ' . $mail->errorInfo();
        }

        return $return;
    }


    public static function setUserState(Db $db, int $userId, int $state): void {
        $formPacket = [];
        $formPacket['oldVal_userId'] = $userId;
        $formPacket['state'] = $state;
        $formPacket['openTS'] = date('Y-m-d H:i:s');

        $save = new SaveData($db, 1, 'user');
        $save->save($formPacket);
        unset ($save);
    }
}
