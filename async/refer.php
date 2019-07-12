<?php

require_once('auth.php');

$loggedIn = $_GET['login'];

if ($loggedIn && $GLOBALS['loggedin']) {
    header('Location: https://edit.biblewiki.one');
} else {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . '?login=failed');
}
