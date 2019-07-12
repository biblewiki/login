<?php
require_once('auth'.php);
require_once('settings.php');

$loggedIn = $_GET['login'];

if ($loggedIn && $GLOBALS['loggedin']) {
    header('Location: '.EDIT_HOST);
} else {
    header('Location: '.LOGIN_HOST.'?login=error');
}
