<?php
require_once('settings.php');
session_start();

if (!$_SESSION['loggedin']) {
    header('Location: ' . LOGIN_HOST . '/logout.php?login=not_logged_in');
} elseif (!$_COOKIE['LOGGEDIN']) {
    header('Location: ' . LOGIN_HOST . '/logout.php?login=expired');
} else {
    $domain = ".".HOST_DOMAIN;
    setcookie("LOGGEDIN", $_COOKIE['LOGGEDIN'], time() + 1800, '/', $domain);
    setcookie("ID", $_COOKIE['ID'], time() + 1800, '/', $domain);
    setcookie("FIRSTNAME", $_COOKIE['FIRSTNAME'], time() + 1800, '/', $domain);
    setcookie("LASTNAME", $_COOKIE['LASTNAME'], time() + 1800, '/', $domain);
    setcookie("LEVEL", $_COOKIE['LEVEL'], time() + 1800, '/', $domain);
    setcookie("PICTURE", $_COOKIE['PICTURE'], time() + 1800, '/', $domain);
}
