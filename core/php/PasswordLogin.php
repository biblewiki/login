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

        // Abfrage vorbereiten
        $st = new SqlSelector('user');
        $st->addSelectElement('user.userId');
        $st->addSelectElement('role.roleType');
        $st->addSelectElement('user.password');
        $st->addSelectElement('user.passwordState');

        $st->addFromElement('INNER JOIN role ON role.roleId = user.roleId');

        $st->addWhereElement('username = :username');

        $st->addParam(':username', $formPacket['username'], \PDO::PARAM_STR);
        $row = $st->execute($db, false);

        // Überprüfen ob der Benutzername existiert
        if ($row) {

            // Überprüfen ob das Passwort übereinstimmt
            if (password_verify($formPacket['password'], $row['password'])) {
                $return['success'] = true;
                $return['passwordState'] = $row['passwordState'];
                $return['userId'] = $row['userId'];
                $return['roleType'] = $row['roleType'];
            } else {
                $return['success'] = false;
                $return['warnMsg'] = 'Benutzername oder Passwort ist falsch';
            }
        } else {
            $return['success'] = false;
            $return['warnMsg'] = 'Benutzername oder Passwort ist falsch';
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

        // Überprüfen ob Email oder Benutzernameschon registriert
        $st = new SqlSelector('user');
        $st->addSelectElement('COUNT(username) AS countUsername');
        $st->addSelectElement('COUNT(email) AS countEmail');
        $st->addWhereElement('email = :email OR username = :username');
        $st->addParam(':username', $formPacket['username']);
        $st->addParam(':email', $formPacket['email']);
        $row = $st->execute($db);
        unset ($st);

        // Überprüfen ob Email schon existiert
        if ($row[0]['countUsername']) {
            $return['errorMsg'] = 'Username existiert bereits';

        // Überprüfen ob Email schon existiert
        } elseif ($row[0]['countEmail']) {
            $return['errorMsg'] = 'Emailadresse ist bereits registriert';

        } elseif ($formPacket['password'] !== $formPacket['passwordRepeat']) {
            $return['errorMsg'] = 'Passwörter stimmen nicht überein';

        // Neuer User hinzufügen
        } else {
            $formPacket['password'] = password_hash($formPacket['password'], PASSWORD_DEFAULT);

            $save = new SaveData($db, 1, 'user');
            $save->save($formPacket);
            $userId = $save->getPrimaryKey()->value;
            unset ($save);

            $return['success'] = !!$userId;
            $return['userId'] = $userId;
            $return['roleId'] = 1; // Standard
            $return['infoMsg'] = 'Registrierung erfolgreich';
        }

        return $return;
    }
}
