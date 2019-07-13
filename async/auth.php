<?php
require_once('settings.php');
session_start();

if (!$_SESSION['loggedin'] || !$_COOKIE['LOGGEDIN']) {
    header('Location: '.LOGIN_HOST.'/logout.php?login=expired');
}
