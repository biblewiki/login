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

    // Funktion auslesen
    $function = $jsonTx->action;

    // Überprüfen ob die Funktion existiert
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

        // Daten auslesen
        $data = json_decode(json_encode($jsonTx->data), true);

        // Funktion ausführen
        $return = json_encode($function($db, $session, $data, $biwi_config));

        // Session schreiben
        $_SESSION["biwi"] = $session;

        // Daten zurückgeben
        echo $return;
        exit;
    } else {
        $return = ['errorMsg' => 'Funktion nicht verfügbar'];
        echo json_encode($return);
        exit;
    }
    exit;
}


/**
 * Google Login
 * @param Db $db
 * @param Session $session
 * @param array $data
 * @param array $config
 * @return array
 */
function checkGoogleLogin(Db $db, Session $session, array $data, array $config): array {

    // Benötigte Skripts einbinden
    require_once 'GoogleLogin.php';
    require_once 'GoogleApi.php';

    // Google API
    $gapi = new GoogleApi();

    // Google Access Token holen
    $accessToken = $gapi->GetAccessToken($config['bot']['googleClientId'], $config['bot']['googleRedirectUri'], $config['bot']['googleClientSecret'], $data['code']);

    // Überprüfen ob der Token erfolgreich angefragt werden konnte
    if ($accessToken['success']) {

        // User Infos holen
        $userInfo = $gapi->GetUserProfileInfo($accessToken['token']);

        // Überprüfen ob die User Infos erfolgreich abgefragt werden konnten
        if ($userInfo['success']) {

            // Login überprüfen
            $return = GoogleLogin::checkLogin($db, $userInfo['userData']);

            // Überprüfen ob das Login erfolgreich war
            if ($return['success']) {

                // Session Daten schreiben
                $session->userId = $return['userId'];
                $session->userRole = $return['roleType'];
                $session->loginType = 'google';

                setLastLogin($db, $return['userId']);

                // Überprüfen ob eine Weiterleitungs URL vorhanden ist
                if ($data['referrer']) {
                    $return['url'] = parse_url($data['referrer']);

                // Weiterleiten zur Edit-Seite
                } else {
                    $return['url'] = $config['url']['edit'];
                }

            // Überprüfen ob ein User erstellt werden soll
            } elseif ($config['bot']['createUserIfNotExist']) {

                // Registrierung
                $return = GoogleLogin::register($db, $userInfo['userData']);

                // Überprüfen ob die Registrierung erfolgreich war
                if ($return['success']) {
                    $session->userId = $return['userId'];
                    $session->userRole = $return['roleType'];
                    $session->loginType = 'google';

                    setLastLogin($db, $return['userId']);
                }

                // Überprüfen ob eine Weiterleitungs URL vorhanden ist
                if ($data['referrer']) {
                    $return['url'] = parse_url($data['referrer']);

                // Weiterleiten zur Edit-Seite
                } else {
                    $return['url'] = $config['url']['edit'];
                }
            }
        } else {
            $return = $userInfo;
        }
    } else {
        $return = $accessToken;
    }

    return $return;
}


/**
 * Passwort Login
 * @param Db $db
 * @param Session $session
 * @param array $data
 * @param array $config
 * @return array
 */
function checkPasswordLogin(Db $db, Session $session, array $data, array $config): array {

    require_once 'PasswordLogin.php';

    $return = PasswordLogin::checkLogin($db, $data);

    if ($return['success']) {
        $session->userId = $return['userId'];
        $session->userRole = $return['roleType'];
        $session->loginType = 'password';

        setLastLogin($db, $return['userId']);

        if ($data['referrer']) {
            $return['url'] = parse_url($data['referrer']);
        } else {
            $return['url'] = $config['url']['edit'];
        }
    }

    return $return;
}


/**
 * Telegram Login
 * @param Db $db
 * @param Session $session
 * @param array $data
 * @param array $config
 * @return array
 */
function checkTelegramLogin(Db $db, Session $session, array $data, array $config): array {

    // Benötigte Skripts einbinden
    require_once 'TelegramLogin.php';
    require_once 'TelegramApi.php';

    // Telegram API
    $tapi = new TelegramApi();

    // Daten validieren
    $authorisation = $tapi->checkTelegramAuthorization($config['bot']['telegramBotToken'], $data['params']);

    // Wenn Daten Valid sind
    if ($authorisation['success']) {

        // Login versuchen
        $return = TelegramLogin::checkLogin($db, $data['params']);

        // Überprüfen ob das Login erfolgreich war
        if ($return['success']) {

            // Session Daten schreiben
            $session->userId = $return['userId'];
            $session->userRole = $return['roleType'];
            $session->loginType = 'telegram';

            setLastLogin($db, $return['userId']);

            // Überprüfen ob eine Weiterleitungs URL vorhanden ist
            if ($data['referrer']) {
                $return['url'] = parse_url($data['referrer']);

            // Weiterleiten zur Edit-Seite
            } else {
                $return['url'] = $config['url']['edit'];
            }

        // Überprüfen ob ein User erstellt werden soll
        } elseif ($config['bot']['createUserIfNotExist']) {

            // Registrierung
            $return = TelegramLogin::register($db, $data['params']);

            // Überprüfen ob die Registrierung erfolgreich war
            if ($return['success']) {
                $session->userId = $return['userId'];
                $session->userRole = $return['roleType'];
                $session->loginType = 'telegram';

                setLastLogin($db, $return['userId']);
            }

            // Überprüfen ob eine Weiterleitungs URL vorhanden ist
            if ($data['referrer']) {
                $return['url'] = parse_url($data['referrer']);

            // Weiterleiten zur Edit-Seite
            } else {
                $return['url'] = $config['url']['edit'];
            }
        }
    } else {
        $return = $authorisation;
    }

    return $return;
}


/**
 * Überprüft die Bestätigung der Emailadresse
 * @param Db $db
 * @param Session $session
 * @param array $data
 * @param array $config
 * @return array
 */
function confirmEmail(Db $db, Session $session, ?array $data, array $config): array {
    require_once 'PasswordLogin.php';
    return PasswordLogin::confirmEmail($db, $data);
}


/**
 * Gibt den Google Auth Link zurück
 * @param Db $db
 * @param Session $session
 * @param array $data
 * @param array $config
 * @return array
 */
function getGoogleAuthLink(Db $db, Session $session, ?array $data, array $config): array {

    $return['success'] = !!$config;
    $return['errorMsg'] = $config ? null : 'Config nicht gefunden';
    $return['url'] = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email') . '&redirect_uri=' . urlencode($config['bot']['googleRedirectUri']) . '&response_type=code&client_id=' . $config['bot']['googleClientId'] . '&access_type=online';

    return $return;
}


/**
 * Gibt den Telegram Button zurück
 * @param Db $db
 * @param Session $session
 * @param array $data
 * @param array $config
 * @return array
 */
function getTelegramButton(Db $db, Session $session, ?array $data, array $config): array {

        $return['success'] = !!$config;
        $return['errorMsg'] = $config ? null : 'Config nicht gefunden';
        $return['button'] = '<script async src="https://telegram.org/js/telegram-widget.js?7" data-telegram-login="' . $config['bot']['telegramBotName'] . '" data-size="large" data-auth-url="' . $config['bot']['telegramRedirectUri'] . '" data-request-access="write"></script>';
        $return['button'] = '<script async src="https://telegram.org/js/telegram-widget.js?6" data-telegram-login="' . $config['bot']['telegramBotName'] . '" data-size="large" data-userpic="false" data-radius="3" data-auth-url="' . $config['bot']['telegramRedirectUri'] . '" data-request-access="write"></script>';

        return $return;
}


/**
 * Passwort User registrieren
 * @param Db $db
 * @param Session $session
 * @param array $data
 * @param array $config
 * @return array
 */
function registerPasswordUser(Db $db, Session $session, array $data, array $config): array {

    require_once 'PasswordLogin.php';

    $return = PasswordLogin::register($db, $data);

    if ($return['success']) {

        $return = PasswordLogin::sendRegisterEmail($return, $config);

        if ($return['success']) {
            $link = '';
            $link .= $_SERVER['HTTPS'] ? 'https://' : 'http://';
            $link .= $_SERVER['HTTP_HOST'];
            $return['url'] = $link;
        }
    }

    return $return;
}


/**
 * Setz das Last Login in der DB
 * @param Db $db
 * @param int $userId
 * @return void
 */
function setLastLogin(Db $db, int $userId): void {

    $formPacket = [];
    $formPacket['oldVal_userId'] = $userId;
    $formPacket['lastLogin'] = date('Y-m-d H:i:s');
    $formPacket['openTS'] = date('Y-m-d H:i:s');

    $save = new SaveData($db, 1, 'user');
    $save->save($formPacket);
    unset ($save);

}
