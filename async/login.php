<?php
// User Datenbank Logindaten einbinden
require_once ('/home/mwepf1gm/www/biblewiki.one/config/db_biblewiki_users.php');

// Datenbank Classe einbinden
require_once dirname(__FILE__) . "/../lib/db.class.php";

// AJAX Input decodieren
$jsonTx = json_decode(file_get_contents("php://input"));

// Überprüfen ob eine Action gefordert wird
if($jsonTx->action != ""){
    try {
        $function = $jsonTx->action;
        echo $function($jsonTx->data, $userID); // Funktion ausführen
        exit;
    }
    catch(Exception $e){
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

// Logindaten Web überprüfen
function CheckLoginWeb($data){
    try {
        // Passwort Versalzen
        $salt = '_biblewikiloginsalt255%';
        $salt_passwort = $data->passwort.$salt;
        $user_passwort = hash('sha256', $salt_passwort);

        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL,USER_DB_USER,USER_DB_PW,USER_DB);
        $stmt = $_db->getDB()->stmt_init();
        
        // Select definieren
        $stmt = $_db->prepare("SELECT
        ".USER_DB.".users.user_password,
        ".USER_DB.".users.user_ID
        FROM ".USER_DB.".users 
        WHERE ".USER_DB.".users.user_username = ?
        GROUP BY ".USER_DB.".users.user_ID;"
        );

        $stmt->bind_param("s", $data->benutzername);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        // Überprüfen ob Benutzer existiert
        if (isset($array[0]['user_ID'])){
            $result = CheckPassword($array[0]['user_password'], $user_passwort, $array[0]['user_ID']);

            return json_encode(array('result' => $result));
        } else {
            return json_encode(array('result' => 'No User'));
        }
    }
    catch(Exception $e){
        return json_encode(array('error' => $e->getMessage()));
    }
}

// Überprüfen ob Google User schon existiert
function CheckGoogleUser($userData){
    try {

        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL,USER_DB_USER,USER_DB_PW,USER_DB);
        $stmt = $_db->getDB()->stmt_init();
        
        // Select definieren
        $stmt = $_db->prepare("SELECT
        ".USER_DB.".users.user_ID
        FROM ".USER_DB.".users 
        WHERE ".USER_DB.".users.id_google = ?;"
        );

        $stmt->bind_param("s", $userData['id']);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        // Überprüfen ob Benutzer existiert
        if (isset($array[0]['user_ID'])){
            $result = GetUserData($array[0]['user_ID']);

            return $result;
        } else {
            
            $result = AddGoogleUser($userData);

            return $result;
        }
    }
    catch(Exception $e){
        return json_encode(array('error' => $e->getMessage()));
    }
}

// Passwort überprüfen
function CheckPassword($password, $passwordCheck, $userID){

    // Überprüfen ob das Passwort stimmt
    if($password === $passwordCheck){

        $userData = GetUserData($userID);

        return $userData;
    } else {
        return 'Wrong Password';
    }

}

// Benutzerinfos abrufen
function GetUserData($userID){
    try {
        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL,USER_DB_USER,USER_DB_PW,USER_DB);
        $stmt = $_db->getDB()->stmt_init();
        
        // Select definieren
        $stmt = $_db->prepare("SELECT
        ".USER_DB.".users.user_ID,
        ".USER_DB.".users.user_firstname,
        ".USER_DB.".users.user_lastname,
        ".USER_DB.".users.user_level,
        ".USER_DB.".users.user_picture
        FROM ".USER_DB.".users 
        WHERE ".USER_DB.".users.user_ID = ?;"
        );
    
        $stmt->bind_param("i", $userID);
    
        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        $result = SessionStart($userID, $array);
    
        return $result;
    }
    catch(Exception $e){
        return array('error' => $e->getMessage());
    }
}

function SessionStart($userID, $userData){

    // Session starten
    session_start();

    $_SESSION["login"] = true;
    $_SESSION["ID"] = $userID;
    $_SESSION["firstname"] = $userData[0]['user_firstname'];
    $_SESSION["lastname"] = $userData[0]['user_lastname'];
    $_SESSION["level"] = $userData[0]['user_level'];
    $_SESSION["picture"] = $userData[0]['user_picture'];

    return "Session started";
}


function AddGoogleUser($userData){
    try {
        $defaultLevel = '50';
        $defaultGoogleState = '50';

        $_db = new db(USER_DB_URL,USER_DB_USER,USER_DB_PW,USER_DB);
        $stmt = $_db->getDB()->stmt_init();
        
        $stmt = $_db->prepare("INSERT INTO ".USER_DB.".users (user_username, user_firstname, user_lastname, user_level, user_email, user_state, user_picture, id_google) VALUES (?,?,?,?,?,?,?,?);");
    
        $stmt->bind_param("sssisisi", $userData['email'], $userData['given_name'], $userData['family_name'], $defaultLevel, $userData['email'], $defaultGoogleState, $userData['picture'], $userData['id']);
    
        $stmt->execute();

        $stmt = $_db->prepare("SELECT LAST_INSERT_ID();");

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        $userID = $array[0]['LAST_INSERT_ID()'];

        return $userData = GetUserData($userID);
    }
    catch(Exception $e){
        return array('error' => $e->getMessage());
    }

}
?>