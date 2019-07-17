<?php
// User Datenbank Logindaten einbinden
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
require_once($homedir . '/config/biblewiki/db_biblewiki_users.php');

// PHPMailer Pfad definieren
$PHPMailerDir = $homedir . '/www/biblewiki.one/mail/PHPMailer';

require $PHPMailerDir . '/src/Exception.php';
require $PHPMailerDir . '/src/PHPMailer.php';
require $PHPMailerDir . '/src/SMTP.php';

// Log-Script einbinden
require_once dirname(__FILE__) . '/log.php';

// Datenbank Klasse einbinden
require_once dirname(__FILE__) . "/../lib/db.class.php";

// AJAX Input decodieren
$jsonTx = json_decode(file_get_contents("php://input"));

// Überprüfen ob eine Action gefordert wird
if ($jsonTx->action != "") {
    try {
        $function = $jsonTx->action;
        echo $function($jsonTx->data); // Funktion ausführen
        exit;
    } catch (Exception $e) {
        $ret = array('error' => 'Action not available');
        echo json_encode($ret);
        exit;
    }
    exit;
}


##############################################################################
#   Passwort Login
##############################################################################

// Logindaten Web überprüfen
function CheckPasswordUser($data)
{

    // Passwort Versalzen
    $salt = '_biblewikiloginsalt255%';
    $salt_passwort = $data->passwort . $salt;
    $user_passwort = hash('sha256', $salt_passwort);

    try {
        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        // Select definieren
        $stmt = $_db->prepare(
            "SELECT
        " . USER_DB . ".users.user_ID,
        " . USER_DB . ".users.user_password,
        " . USER_DB . ".users.user_email_state,
        " . USER_DB . ".users.email_token
        FROM " . USER_DB . ".users 
        WHERE " . USER_DB . ".users.user_username = ? OR " . USER_DB . ".users.user_email = ?
        GROUP BY " . USER_DB . ".users.user_ID;"
        );

        $stmt->bind_param("ss", $data->benutzername, $data->benutzername);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        // Überprüfen ob Benutzer existiert
        if (isset($array[0]['user_ID'])) {

            $result = CheckData($array[0]['user_password'], $user_passwort, $array[0]['user_ID'], $array[0]['user_email_state'], $array[0]['email_token']);

            if ($result === 'loggedin') {
                return json_encode(array('success' => $result));
            } else {
                return json_encode(array('error' => $result));
            }
        } else {
            return json_encode(array('action' => 'Register'));
        }
    } catch (Exception $e) {
        return json_encode(array('error' => $e->getMessage()));
    }
}

function AddPasswordUser($data)
{

    // Passwort Versalzen
    $salt = '_biblewikiloginsalt255%';
    $salt_passwort = $data->passwort . $salt;
    $user_passwort = hash('sha256', $salt_passwort);

    //Generate a random string.
    $token = openssl_random_pseudo_bytes(32);

    //Convert the binary data into hexadecimal representation.
    $token = bin2hex($token);

    try {
        $defaultLevel = '50';
        $defaultPasswortState = '100';
        $defaultEmailState = '10';
        $defaultUserState = '30';
        $defaultPicture = 'img/silhouette.png';

        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        $stmt = $_db->prepare("INSERT INTO " . USER_DB . ".users (user_username, user_firstname, user_lastname, user_level, user_email, email_token, user_email_state, user_password, user_state, user_pw_state, user_picture) VALUES (?,?,?,?,?,?,?,?,?,?,?);");

        $stmt->bind_param("sssissisiis", $data->benutzername, $data->vorname, $data->nachname, $defaultLevel, $data->email, $token, $defaultEmailState, $user_passwort, $defaultUserState, $defaultPasswortState, $defaultPicture);

        $stmt->execute();

        // Eingefügte ID auslesen
        $stmt = $_db->prepare("SELECT LAST_INSERT_ID();");

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        $userID = $array[0]['LAST_INSERT_ID()'];

        // Überprüfen ob Benutzer existiert
        if ($userID > 0) {

            UserLog($userID, 'Password', 'Add Password User');

            // Mail Klasse einbinden
            require_once dirname(__FILE__) . "/../lib/mail.class.php";

            // Email bestätigen HTML einbinden
            require_once dirname(__FILE__) . "/../lib/mail/confirm_email_html.php";

            $mail = new mail();

            $mail->set_to_email($data->email);
            $mail->set_to_name($data->vorname . ' ' . $data->nachname);
            $mail->set_subject('BibleWiki | Email Adresse bestätigen');
            $mail->set_body($confirm_email_html);
            $result = $mail->send_mail();

            if ($result === 'success') {
                UserLog($userID, 'Password', 'Send confirm mail address email ' . $result);

                return json_encode(array('action' => 'confirm_password'));
            } else {
                UserLog($userID, 'Password', 'Send confirm mail address email failed', $result);

                return json_encode(array('error' => 'email_failed'));
            }
        } else {
            return json_encode(array('error' => 'add_user_failed'));
        }
    } catch (Exception $e) {
        return json_encode(array('error' => $e->getMessage()));
    }
}

// Passwort überprüfen
function CheckData($password, $passwordCheck, $userID, $emailState, $emailToken)
{

    // Überprüfen ob das Passwort stimmt
    if ($password === $passwordCheck) {
        if ($emailState === 100 && $emailToken == '') {
            $userData = GetUserData($userID);
            $result = SessionStart($userID, $userData, 'Password');

            return $result;
        } else {
            UserLog($userID, 'Password', 'Email address not yet confirmed');
            return 'email_not_confirmed';
        }
    } else {
        UserLog($userID, 'Password', 'Wrong Password');
        return 'wrong_password';
    }
}

// Passwort reseten
function RequestResetPassword($data)
{
    try {
        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        // Select definieren
        $stmt = $_db->prepare(
            "SELECT
        " . USER_DB . ".users.user_ID,
        " . USER_DB . ".users.user_firstname,
        " . USER_DB . ".users.user_lastname,
        " . USER_DB . ".users.user_email
        FROM " . USER_DB . ".users 
        WHERE " . USER_DB . ".users.user_username = ? AND " . USER_DB . ".users.user_email = ?;"
        );

        $stmt->bind_param("is", $data->benutzername, $data->email);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        $userID = $array[0]['user_ID'];

        // Überprüfen ob Benutzer existiert
        if ($userID > 0) {

            UserLog($userID, 'Password', 'Asked for password reset');

            $result = TokenResetPassword($array);

            return json_encode(array('success' => $result));
        } else {
            return json_encode(array('error' => 'user_not_exist'));
        }
    } catch (Exception $e) {
        return json_encode(array('error' => $e->getMessage()));
    }
}

function TokenResetPassword($data)
{
    $userID = $data[0]['user_ID'];

    //Generate a random string.
    $token = openssl_random_pseudo_bytes(32);

    //Convert the binary data into hexadecimal representation.
    $token = bin2hex($token);

    $passwordState = 50;

    try {
        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        // Select definieren
        $stmt = $_db->prepare(
            "UPDATE
        " . USER_DB . ".users
        SET " . USER_DB . ".users.pw_token = ?,
        " . USER_DB . ".users.pw_timestamp = ?,
        " . USER_DB . ".users.user_pw_state = ?
        WHERE " . USER_DB . ".users.user_ID = ?;"
        );

        $stmt->bind_param("ssii", $token, date("Y-m-d H:i:s"), $passwordState, $userID);

        $stmt->execute();

        UserLog($userID, 'Password', 'Set password reset token');

        // Mail Klasse einbinden
        require_once dirname(__FILE__) . "/../lib/mail.class.php";

        // Email bestätigen HTML einbinden
        require_once dirname(__FILE__) . "/../lib/mail/reset_password_html.php";

        $mail = new mail();

        $mail->set_to_email($data[0]['user_email']);
        $mail->set_to_name($data[0]['user_firstname'] . ' ' . $data[0]['user_lastname']);
        $mail->set_subject('BibleWiki | Passwort zurücksetzen bestätigen');
        $mail->set_body($reset_password_html);
        $result = $mail->send_mail();

        if ($result === 'success') {
            UserLog($userID, 'Password', 'Send confirm password reset mail success');
        }

        return $result;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function CheckPasswordToken($userID, $token)
{
    UserLog($userID, 'Password', 'Password reset Link opened');
    try {
        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        // Select definieren
        $stmt = $_db->prepare(
            "SELECT
        " . USER_DB . ".users.pw_token
        FROM " . USER_DB . ".users 
        WHERE " . USER_DB . ".users.user_ID = ?;"
        );

        $stmt->bind_param("i", $userID);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        // Überprüfen ob Benutzer existiert
        if ($array[0]['pw_token'] === $token) {
            UserLog($userID, 'Password', 'Password reset token valid');
            return 'valid';
        } else {
            UserLog($userID, 'Password', 'Password reset wrong token');
            return 'wrong_token';
        }
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function ResetPassword($data)
{
    session_start();

    $userID = $data->user;

    if ($_SESSION['password_token'] === $data->token && $_COOKIE['PASSWORD_TOKEN'] === $data->token) {
        if ($_SESSION["password_user"] === $userID && $_COOKIE['PASSWORD_USER'] === $userID && $_SESSION['token_valid'] === $_COOKIE['TOKEN_VALID']) {

            // Passwort Versalzen
            $salt = '_biblewikiloginsalt255%';
            $salt_passwort = $data->passwort . $salt;
            $user_passwort = hash('sha256', $salt_passwort);

            $passwordState = 100;

            try {
                // Datenbankverbindung herstellen
                $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
                $stmt = $_db->getDB()->stmt_init();

                // Select definieren
                $stmt = $_db->prepare(
                    "UPDATE
        " . USER_DB . ".users
        SET " . USER_DB . ".users.user_password = ?,
        " . USER_DB . ".users.pw_token = NULL,
        " . USER_DB . ".users.pw_timestamp = NULL,
        " . USER_DB . ".users.user_pw_state = ?
        WHERE " . USER_DB . ".users.user_ID = ?;"
                );

                $stmt->bind_param("ssi", $user_passwort, $passwordState, $userID);

                $stmt->execute();

                UserLog($userID, 'Password', 'Reset password sucess');

                unset($_SESSION["password_token"]);
                unset($_SESSION["password_user"]);
                unset($_SESSION["token_valid"]);

                unset($_COOKIE['PASSWORD_TOKEN']);
                setcookie("PASSWORD_TOKEN", '', time() - 3600, '/');
                unset($_COOKIE['PASSWORD_USER']);
                setcookie("PASSWORD_USER", '', time() - 3600, '/');
                unset($_COOKIE['TOKEN_VALID']);
                setcookie("TOKEN_VALID", '', time() - 3600, '/');

                // Mail Klasse einbinden
                require_once dirname(__FILE__) . "/../lib/mail.class.php";

                // Email bestätigen HTML einbinden
                require_once dirname(__FILE__) . "/../lib/mail/reset_password_confirmed_html.php";

                $userData = GetUserData($userID);

                $mail = new mail();

                $mail->set_to_email($userData['user_email']);
                $mail->set_to_name($userData['user_firstname'] . ' ' . $userData['user_lastname']);
                $mail->set_subject('BibleWiki | Passwort wurde zurückgesetzt');
                $mail->set_body($reset_password_confirmed_html);
                $result = $mail->send_mail();

                if ($result === 'success') {
                    UserLog($userID, 'Password', 'Send password reset successfull mail success');
                }

                return json_encode(array('success' => $result));
            } catch (Exception $e) {
                return json_encode(array('error' => $e->getMessage()));
            }
        } else {
            UserLog($userID, 'Password', 'Password reset failed', 'Cookie and session variables not match');
            return json_encode(array('error' => 'pw_reset_failed'));
        }
    } else {
        UserLog($userID, 'Password', 'Password reset failed', 'Session expired');
        return json_encode(array('error' => 'pw_reset_token_expired'));
    }
}

function CheckEmailToken($userID, $token)
{
    try {
        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        // Select definieren
        $stmt = $_db->prepare(
            "SELECT
        " . USER_DB . ".users.email_token
        FROM " . USER_DB . ".users 
        WHERE " . USER_DB . ".users.user_ID = ?;"
        );

        $stmt->bind_param("i", $userID);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        // Überprüfen ob Benutzer existiert
        if ($array[0]['email_token'] === $token) {
            return ConfirmUserEmail($userID, $token);
        } else {
            return 'failed';
        }
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function ConfirmUserEmail($userID, $token)
{

    $emailState = 100;

    try {
        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        // Select definieren
        $stmt = $_db->prepare(
            "UPDATE
        " . USER_DB . ".users
        SET " . USER_DB . ".users.email_token = '',
        " . USER_DB . ".users.user_email_state = ?
        WHERE " . USER_DB . ".users.user_ID = ?;"
        );

        $stmt->bind_param("ii", $emailState, $userID);

        $stmt->execute();


        return 'success';
    } catch (Exception $e) {
        return $e->getMessage();
    }
}


##############################################################################
#   Google
##############################################################################

// Überprüfen ob Google User schon existiert
function CheckGoogleUser($userData)
{
    try {

        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        // Select definieren
        $stmt = $_db->prepare(
            "SELECT
        " . USER_DB . ".users.user_ID
        FROM " . USER_DB . ".users 
        WHERE " . USER_DB . ".users.id_google = ?;"
        );

        $stmt->bind_param("s", $userData['id']);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        // Überprüfen ob Benutzer existiert
        if (isset($array[0]['user_ID'])) {
            $userData = GetUserData($array[0]['user_ID']);
            $result = SessionStart($array[0]['user_ID'], $userData, 'Google');

            return $result;
            // Wenn nicht, wird er erstellt
        } else {

            $result = AddGoogleUser($userData);

            return $result;
        }
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function AddGoogleUser($userData)
{
    try {
        // Standardwerte definieren
        $defaultLevel = '50';
        $defaultGoogleState = '50';
        $defaultGoogleEmailState = '100';

        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        $stmt = $_db->prepare("INSERT INTO " . USER_DB . ".users (user_username, user_firstname, user_lastname, user_level, user_email, user_email_state, user_state, user_picture, id_google) VALUES (?,?,?,?,?,?,?,?,?);");

        $stmt->bind_param("sssisiisi", $userData['email'], $userData['given_name'], $userData['family_name'], $defaultLevel, $userData['email'], $defaultGoogleEmailState, $defaultGoogleState, $userData['picture'], $userData['id']);

        $stmt->execute();

        // Eingefügte ID auslesen
        $stmt = $_db->prepare("SELECT LAST_INSERT_ID();");

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        $userID = $array[0]['LAST_INSERT_ID()'];

        UserLog($userID, 'Google', 'Add Google User');

        // Userdaten auslesen und dann Session starten
        $userData = GetUserData($userID);
        $result = SessionStart($userID, $userData, 'Google');

        return $result;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

##############################################################################
#   Telegram
##############################################################################

// Überprüfen ob Telegram User schon existiert
function CheckTelegramUser($userData)
{
    try {

        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        // Select definieren
        $stmt = $_db->prepare(
            "SELECT
        " . USER_DB . ".users.user_ID
        FROM " . USER_DB . ".users 
        WHERE " . USER_DB . ".users.id_telegram = ?;"
        );

        $stmt->bind_param("s", $userData['id']);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        // Überprüfen ob Benutzer existiert
        if (isset($array[0]['user_ID'])) {
            $userData = GetUserData($array[0]['user_ID'], 'Telegram');
            $result = SessionStart($array[0]['user_ID'], $userData, 'Telegram');

            return $result;
            // Wenn nicht, wird er erstellt
        } else {
            $result = AddTelegramUser($userData);

            return $result;
        }
    } catch (Exception $e) {
        return json_encode(array('error' => $e->getMessage()));
    }
}

function AddTelegramUser($userData)
{

    try {
        // Standardwerte definieren
        $defaultLevel = '50';
        $defaultTelegramState = '30';

        $username = (isset($userData['username']) ? $userData['username'] : $userData['id']);
        $photo_url = (isset($userData['photo_url']) ? $userData['photo_url'] : 'img/silhouette.png');

        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        $stmt = $_db->prepare("INSERT INTO " . USER_DB . ".users (user_username, user_firstname, user_lastname, user_level, user_state, user_picture, id_telegram) VALUES (?,?,?,?,?,?,?);");

        $stmt->bind_param("sssiisi", $username, $userData['first_name'], $userData['last_name'], $defaultLevel, $defaultTelegramState, $photo_url, $userData['id']);

        $stmt->execute();

        // Eingefügte ID auslesen
        $stmt = $_db->prepare("SELECT LAST_INSERT_ID();");

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        $userID = $array[0]['LAST_INSERT_ID()'];

        UserLog($userID, 'Telegram', 'Add Telegram User');

        // Userdaten auslesen und dann Session starten
        $userData = GetUserData($userID, 'Telegram');
        $result = SessionStart($userID, $userData, 'Telegram');

        return $result;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

##############################################################################
#   Allgemein
##############################################################################

// Benutzerinfos abrufen
function GetUserData($userID)
{
    try {
        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        // Select definieren
        $stmt = $_db->prepare(
            "SELECT
        " . USER_DB . ".users.user_ID,
        " . USER_DB . ".users.user_firstname,
        " . USER_DB . ".users.user_lastname,
        " . USER_DB . ".users.user_email,
        " . USER_DB . ".users.user_level,
        " . USER_DB . ".users.user_picture
        FROM " . USER_DB . ".users 
        WHERE " . USER_DB . ".users.user_ID = ?;"
        );

        $stmt->bind_param("i", $userID);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        return $array[0];
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function SessionStart($userID, $userData, $method)
{

    // Session starten
    session_start();

    $_SESSION["loggedin"] = true;
    $_SESSION["id"] = $userID;
    $_SESSION["firstname"] = $userData['user_firstname'];
    $_SESSION["lastname"] = $userData['user_lastname'];
    $_SESSION["level"] = $userData['user_level'];
    $_SESSION["picture"] = $userData['user_picture'];

    setcookie("LOGGEDIN", 'true', time() + 1800, '/', ".biblewiki.one", 0);
    setcookie("ID", $userID, time() + 1800, '/', ".biblewiki.one", 0);
    setcookie("FIRSTNAME", $_SESSION["firstname"], time() + 1800, '/', ".biblewiki.one", 0);
    setcookie("LASTNAME", $_SESSION["lastname"], time() + 1800, '/', ".biblewiki.one", 0);
    setcookie("LEVEL", $_SESSION["level"], time() + 1800, '/', ".biblewiki.one", 0);
    setcookie("PICTURE", $_SESSION["picture"], time() + 1800, '/', ".biblewiki.one", 0);

    $result = UserLog($userID, $method);

    return "loggedin";
}
