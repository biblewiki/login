<?php
require_once('settings.php');
session_start();

if (!$_SESSION['loggedin']) {
    header('Location: ' . LOGIN_HOST . '/logout.php?login=not_logged_in');
} elseif (!$_COOKIE['LOGGEDIN']) {
    header('Location: ' . LOGIN_HOST . '/logout.php?login=expired');
} else {
    setcookie("LOGGEDIN", $_COOKIE['LOGGEDIN'], time() + 1800, '/', ".biblewiki.one", 0);
    setcookie("ID", $_COOKIE['ID'], time() + 1800, '/', ".biblewiki.one", 0);
    setcookie("FIRSTNAME", $_COOKIE['FIRSTNAME'], time() + 1800, '/', ".biblewiki.one", 0);
    setcookie("LASTNAME", $_COOKIE['LASTNAME'], time() + 1800, '/', ".biblewiki.one", 0);
    setcookie("LEVEL", $_COOKIE['LEVEL'], time() + 1800, '/', ".biblewiki.one", 0);
    setcookie("PICTURE", $_COOKIE['PICTURE'], time() + 1800, '/', ".biblewiki.one", 0);
}
