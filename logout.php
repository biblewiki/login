<?php
// User Datenbank Logindaten einbinden
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
require_once($homedir . '/config/biblewiki/db_biblewiki_users.php');

// Datenbank Classe einbinden
require_once dirname(__FILE__) . "/lib/db.class.php";

require_once dirname(__FILE__) . "/async/log.php";

session_start();

if (isset($_GET['login'])){
    $method = 'Session';
    $action = 'Session expired';
} else {
    $method = 'Button';
    $action = 'logout';
}

UserLog($_SESSION['id'], $method, $action);

// COOKIES löschen
unset($_COOKIE['LOGGEDIN']);
setcookie ("LOGGEDIN", false, time() - 3600, '/', ".biblewiki.one");
unset($_COOKIE['ID']);
setcookie ("ID", '', time() - 3600, '/', ".biblewiki.one");
unset($_COOKIE['FIRSTNAME']);
setcookie ("FIRSTNAME", '', time() - 3600, '/', ".biblewiki.one");
unset($_COOKIE['LASTNAME']);
setcookie ("LASTNAME", '', time() - 3600, '/', ".biblewiki.one");
unset($_COOKIE['LEVEL']);
setcookie ("LEVEL", '', time() - 3600, '/', ".biblewiki.one");
unset($_COOKIE['PICTURE']);
setcookie ("PICTURE", '', time() - 3600, '/', ".biblewiki.one");
unset($_COOKIE['USERNAME']);
setcookie ("USERNAME", '', time() - 3600);

if (isset($_GET['login'])){
    header('Location: /?login='.$_GET['login']);
} else {
    header('Location: /?logout=success');
}
session_destroy();
