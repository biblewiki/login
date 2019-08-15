<?php
require_once('settings.php');
require_once(SCRIPT_PATH.'/php/auth.php');

$loggedIn = $_GET['login'];

if ($loggedIn && isset($_COOKIE['LOGGEDIN']) && isset($_SESSION['loggedin'])) {
    header('Location: '.EDIT_HOST);
} else {
    header('Location: '.LOGIN_HOST.'?notif=login_error');
}
