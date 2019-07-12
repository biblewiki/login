<?php
// User Datenbank Logindaten einbinden
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
require_once($homedir . '/config/biblewiki/db_biblewiki_users.php');

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
        " . USER_DB . ".users.user_password,
        " . USER_DB . ".users.user_ID
        FROM " . USER_DB . ".users 
        WHERE " . USER_DB . ".users.user_username = ?
        GROUP BY " . USER_DB . ".users.user_ID;"
        );

        $stmt->bind_param("s", $data->benutzername);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        // Überprüfen ob Benutzer existiert
        if (isset($array[0]['user_ID'])) {
            $result = CheckPassword($array[0]['user_password'], $user_passwort, $array[0]['user_ID']);

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

    try {
        $defaultLevel = '50';
        $defaultPasswortState = '100';
        $defaultEmailState = '10';
        $defaultUserState = '30';
        $defaultPicture = 'img/silhouette.png';

        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        $stmt = $_db->prepare("INSERT INTO " . USER_DB . ".users (user_username, user_firstname, user_lastname, user_level, user_email, user_email_state, user_password, user_state, user_pw_state, user_picture) VALUES (?,?,?,?,?,?,?,?,?,?);");

        $stmt->bind_param("sssisisiis", $data->benutzername, $data->vorname, $data->nachname, $defaultLevel, $data->email, $defaultEmailState, $user_passwort, $defaultUserState, $defaultPasswortState, $defaultPicture);

        $stmt->execute();

        // Eingefügte ID auslesen
        $stmt = $_db->prepare("SELECT LAST_INSERT_ID();");

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        $userID = $array[0]['LAST_INSERT_ID()'];

        UserLog($userID, 'Password', 'Add Password User');

        // Überprüfen ob Benutzer existiert
        if ($userID > 0) {

            // Userdaten auslesen und dann Session starten
            $userData = GetUserData($userID, 'Password');

            return json_encode(array('action' => 'success'));
        } else {
            return json_encode(array('error' => 'Failed'));
        }
    } catch (Exception $e) {
        return $e->getMessage();
    }
}


// Passwort überprüfen
function CheckPassword($password, $passwordCheck, $userID)
{

    // Überprüfen ob das Passwort stimmt
    if ($password === $passwordCheck) {

        $userData = GetUserData($userID, 'Password');

        return $userData;
    } else {
        UserLog($userID, 'Password', $error = 'Wrong Password');
        return 'wrong_password';
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

    $_SESSION["login"] = true;
    $_SESSION["id"] = $userID;
    $_SESSION["firstname"] = $userData[0]['user_firstname'];
    $_SESSION["lastname"] = $userData[0]['user_lastname'];
    $_SESSION["level"] = $userData[0]['user_level'];
    $_SESSION["picture"] = $userData[0]['user_picture'];

    setcookie ("LOGGEDIN", 'true', time()+3600*24, '/', ".biblewiki.one", 0 );
    setcookie ("ID", $userID, time()+3600*24, '/', ".biblewiki.one", 0 );
    setcookie ("FIRSTNAME", $_SESSION["firstname"], time()+3600*24, '/', ".biblewiki.one", 0 );
    setcookie ("LASTNAME", $_SESSION["lastname"], time()+3600*24, '/', ".biblewiki.one", 0 );
    setcookie ("LEVEL", $_SESSION["level"], time()+3600*24, '/', ".biblewiki.one", 0 );
    setcookie ("PICTURE", $_SESSION["picture"], time()+3600*24, '/', ".biblewiki.one", 0 );

    $result = UserLog($userID, $method);

    return "loggedin";
}

function UserLog($userID, $method, $action = 'login', $error = '')
{

    $hostname = gethostname();

    $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
    $stmt = $_db->getDB()->stmt_init();

    $stmt = $_db->prepare("INSERT INTO " . USER_DB . ".user_log (id_user, ip, hostname, browser, method, action, error) VALUES (?,?,?,?,?,?,?);");

    $stmt->bind_param("issssss", $userID, $_SERVER['REMOTE_ADDR'], $hostname, $_SERVER['HTTP_USER_AGENT'], $method, $action, $error);

    $stmt->execute();
}
