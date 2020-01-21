<?php

// Config einbinden
require '../../config/config.php';
require_once 'Db.php';
require_once 'Session.php';
require_once 'SessHandler.php';
require_once 'QueryBuilderForSelect.php';
require_once 'SqlSelector.php';
require_once 'SaveData.php';

// AJAX Input decodieren
$jsonTx = json_decode(file_get_contents("php://input"));

// Überprüfen ob eine Action gefordert wird
if ($jsonTx->action != "") {

    $function = $jsonTx->action;
    if (function_exists($function)) {

        // DB öffnen
        try {
            $db = new Db(
                $biwi_config['database']['dsn'],
                $biwi_config['database']['user'],
                $biwi_config['database']['password'],
                [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
            );
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die($msg . PHP_EOL);echo $msg;
        }

        // Eigener SessionHandler: Sessions werden in DB gespeichert
        $sessionHandler = new SessHandler($db);

        // Session auslesen
        $session = null;
        session_start();
        if (\array_key_exists("biwi", $_SESSION) && ($_SESSION["biwi"] instanceof Session)) {
            $session = $_SESSION["biwi"];
        } else {
            $session = new Session();
        }

        $data = json_decode(json_encode($jsonTx->data), true);
        echo json_encode($function($db, $session, $data, $biwi_config)); // Funktion ausführen
        $_SESSION["biwi"] = $session;
        exit;
    } else {
        $ret = ['errorMsg' => 'Funktion nicht verfügbar'];
        echo json_encode($ret);
        exit;
    }
    exit;
}


function checkGoogleLogin(Db $db, Session $session, array $data, array $config): array {

    require_once 'GoogleLogin.php';
    require_once 'GoogleApi.php';

    $return = [];

    $gapi = new GoogleApi();

    // Google Access Token holen
    $accessToken = $gapi->GetAccessToken($config['bot']['googleClientId'], $config['bot']['googleRedirectUri'], $config['bot']['googleClientSecret'], $data['code']);

    // User Infos holen
    $user_info = $gapi->GetUserProfileInfo($accessToken['access_token']);

    $login = GoogleLogin::checkLogin($db, $user_info);

    $return['success'] = $login['success'];

    if ($login['success']) {
        $session->userId = $login['userId'];
        $session->userRole = $login['userRole'];

        if ($data['referrer']) {
            $return['url'] = parse_url($data['referrer']);
        } else {
            $return['url'] = $config['url']['edit'];
        }
    } elseif ($config['bot']['createUserIfNotExist']) {
        $register = GoogleLogin::register($db, $user_info);

        $return['success'] = $register['success'];

        if ($register['success']) {
            $session->userId = $login['userId'];
            $session->userRole = $login['userRole'];
        }

        if ($data['referrer']) {
            $return['url'] = parse_url($data['referrer']);
        } else {
            $return['url'] = $config['url']['edit'];
        }
    } else {
        if ($login['warnMsg']) {
            $return['warnMsg'][] = $login['warnMsg'];
        }

        if ($login['errorMsg']) {
            $return['errorMsg'][] = $login['errorMsg'];
        }
    }

    return $return;
}


function checkPasswordLogin(Db $db, Session $session, array $data, array $config): array {

    require_once 'PasswordLogin.php';

    $return = [];
    $login = PasswordLogin::checkLogin($db, $data);

    $return['success'] = $login['success'];

    if ($login['success']) {
        $session->userId = $login['userId'];
        $session->userRole = $login['roleId'];

        if ($data['referrer']) {
            $return['url'] = parse_url($data['referrer']);
        } else {
            $return['url'] = $config['url']['edit'];
        }
    } else {
        if ($login['warnMsg']) {
            $return['warnMsg'][] = $login['warnMsg'];
        }

        if ($login['errorMsg']) {
            $return['errorMsg'][] = $login['errorMsg'];
        }
    }

    return $return;
}

function getGoogleAuthLink(Db $db, Session $session, ?array $data, array $config): array {

    $return['success'] = !!$config;
    $return['url'] = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email') . '&redirect_uri=' . urlencode($config['bot']['googleRedirectUri']) . '&response_type=code&client_id=' . $config['bot']['googleClientId'] . '&access_type=online';

    return $return;
}
