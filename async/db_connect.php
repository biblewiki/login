<?php
// User Datenbank Logindaten einbinden
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
require_once($homedir . '/config/biblewiki/db_biblewiki_users.php');

// Log-Script einbinden
require_once dirname(__FILE__) . '/log.php';

// Datenbank Classe einbinden
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
/*else {
    $ret = array('error' => 'No Action');
    echo json_encode($ret);
    exit();
}*/

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
        " . USER_DB . ".users.emaiL_token
        FROM " . USER_DB . ".users 
        WHERE " . USER_DB . ".users.user_username = ?
        GROUP BY " . USER_DB . ".users.user_ID;"
        );

        $stmt->bind_param("s", $data->benutzername);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        // Überprüfen ob Benutzer existiert
        if (isset($array[0]['user_ID'])) {

            $result = CheckData($array[0]['user_password'], $user_passwort, $array[0]['user_ID'], $array[0]['user_email_state'], $array[0]['emaiL_token']);

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

            // Mail-Script einbinden
            require_once dirname(__FILE__) . "/../mail/send_mail.php";

            // Email bestätigen HTML einbinden
            require_once dirname(__FILE__) . "/../mail/confirm_email_html.php";

            $result = email($userID, NULL, 'Test', $confirm_email_html, '$BodyNoHtml');

            if ($result === 'success') {
                UserLog($userID, 'Password', 'Send confirm mail address email ' . $result);

                return json_encode(array('action' => 'confirm_password'));
            } else {
                UserLog($userID, 'Password', 'Send confirm mail address email failed', $result);

                return json_encode(array('error' => 'email_failed'));
            }
            // Userdaten auslesen und dann Session starten
            // $userData = GetUserData($userID, 'Password');


        } else {
            return json_encode(array('error' => 'Failed'));
        }
    } catch (Exception $e) {
        return $e->getMessage();
    }
}


// Passwort überprüfen
function CheckData($password, $passwordCheck, $userID, $emailState, $emailToken)
{

    // Überprüfen ob das Passwort stimmt
    if ($password === $passwordCheck) {
        if ($emailState === 100 && $emailToken == '') {
            $userData = GetUserData($userID, 'Password');

            return $userData;
        } else {
            UserLog($userID, 'Password', 'Email address not yet confirmed');
            return 'email_not_confirmed';
        }
    } else {
        UserLog($userID, 'Password', 'Wrong Password');
        return 'wrong_password';
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
            $result = GetUserData($array[0]['user_ID'], 'Google');

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
        return $userData = GetUserData($userID, 'Google');
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
            $result = GetUserData($array[0]['user_ID'], 'Telegram');

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
        return $userData = GetUserData($userID, 'Telegram');
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

##############################################################################
#   Allgemein
##############################################################################

// Benutzerinfos abrufen
function GetUserData($userID, $method)
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
        " . USER_DB . ".users.user_level,
        " . USER_DB . ".users.user_picture
        FROM " . USER_DB . ".users 
        WHERE " . USER_DB . ".users.user_ID = ?;"
        );

        $stmt->bind_param("i", $userID);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        $result = SessionStart($userID, $array, $method);

        return $result;
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
    $_SESSION["firstname"] = $userData[0]['user_firstname'];
    $_SESSION["lastname"] = $userData[0]['user_lastname'];
    $_SESSION["level"] = $userData[0]['user_level'];
    $_SESSION["picture"] = $userData[0]['user_picture'];

    setcookie("LOGGEDIN", 'true', time() + 3600 * 24, '/', ".biblewiki.one", 0);
    setcookie("ID", $userID, time() + 3600 * 24, '/', ".biblewiki.one", 0);
    setcookie("FIRSTNAME", $_SESSION["firstname"], time() + 3600 * 24, '/', ".biblewiki.one", 0);
    setcookie("LASTNAME", $_SESSION["lastname"], time() + 3600 * 24, '/', ".biblewiki.one", 0);
    setcookie("LEVEL", $_SESSION["level"], time() + 3600 * 24, '/', ".biblewiki.one", 0);
    setcookie("PICTURE", $_SESSION["picture"], time() + 3600 * 24, '/', ".biblewiki.one", 0);

    $result = UserLog($userID, $method);

    return "loggedin";
}
