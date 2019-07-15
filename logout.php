<?php
// User Datenbank Logindaten einbinden
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
require_once($homedir . '/config/biblewiki/db_biblewiki_users.php');

// Datenbank Classe einbinden
require_once dirname(__FILE__) . "/lib/db.class.php";

session_start();

$result = LoginLog($_SESSION['id'], 'Button');

function LoginLog($userID, $method, $error = '')
{
    try {
        $hostname = gethostname();
        $action = 'logout';

        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        $stmt = $_db->prepare("INSERT INTO " . USER_DB . ".user_log (id_user, ip, hostname, browser, method, action, error) VALUES (?,?,?,?,?,?,?);");

        $stmt->bind_param("issssss", $userID, $_SERVER['REMOTE_ADDR'], $hostname, $_SERVER['HTTP_USER_AGENT'], $method, $action, $error);

        $stmt->execute();

        // Userdaten auslesen und dann Session starten
        return 'success';
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

// COOKIES löschen
unset($_COOKIE['LOGGEDIN']);
setcookie ("LOGGEDIN", false, time() - 3600, '/', ".biblewiki.one", 0 );
unset($_COOKIE['ID']);
setcookie ("ID", '', time() - 3600, '/', ".biblewiki.one", 0 );
unset($_COOKIE['FIRSTNAME']);
setcookie ("FIRSTNAME", 'test', time() - 3600, '/', ".biblewiki.one", 0 );
unset($_COOKIE['LASTNAME']);
setcookie ("LASTNAME", '', time() - 3600, '/', ".biblewiki.one", 0 );
unset($_COOKIE['LEVEL']);
setcookie ("LEVEL", '', time() - 3600, '/', ".biblewiki.one", 0 );
unset($_COOKIE['PICTURE']);
setcookie ("PICTURE", '', time() - 3600, '/', ".biblewiki.one", 0 );
unset($_COOKIE['USERNAME']);
setcookie ("USERNAME", '', time() - 3600);

if (isset($_GET['login'])){
    header('Location: /?logout=sucess&login='.$_GET['login']);
} else {
    header('Location: /?logout=success');
}
session_destroy();
