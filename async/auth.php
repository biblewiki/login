<?php
require_once('settings.php');
session_start();

$sessionHash = hash(sha256, $_SESSION["loggedin"].$_SESSION["id"].$_SESSION["firstname"].$_SESSION["lastname"].$_SESSION["level"].$_SESSION["picture"]);
$cookieHash = hash(sha256, $_COOKIE["LOGGEDIN"].$_COOKIE["ID"].$_COOKIE["FIRSTNAME"].$_COOKIE["LASTNAME"].$_COOKIE["LEVEL"].$_COOKIE["PICTURE"]);

if ($_SESSION["loggedin"] === $_COOKIE["LOGGEDIN"] && $sessionHash === $cookieHash){
    $domain = ".".HOST_DOMAIN;
    setcookie("LOGGEDIN", $_COOKIE['LOGGEDIN'], time() + 1800, '/', $domain);
    setcookie("ID", $_COOKIE['ID'], time() + 1800, '/', $domain);
    setcookie("FIRSTNAME", $_COOKIE['FIRSTNAME'], time() + 1800, '/', $domain);
    setcookie("LASTNAME", $_COOKIE['LASTNAME'], time() + 1800, '/', $domain);
    setcookie("LEVEL", $_COOKIE['LEVEL'], time() + 1800, '/', $domain);
    setcookie("PICTURE", $_COOKIE['PICTURE'], time() + 1800, '/', $domain);
 }elseif ($_SESSION['loggedin'] === '' || !isset($_SESSION['loggedin'])) {
    header('Location: ' . LOGIN_HOST . '/logout.php?notif=not_logged_in');
} elseif ($_COOKIE['LOGGEDIN'] === '' || !isset($_COOKIE['LOGGEDIN'])) {
    header('Location: ' . LOGIN_HOST . '/logout.php?login=expired');
} else {
    header('Location: ' . LOGIN_HOST . '/logout.php?login=session_fail');
}
