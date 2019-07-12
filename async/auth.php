<?php
session_start();

if ($_SESSION['login']){
    $GLOBALS['loggedin'] = true;
} else{
    session_destroy();
    $GLOBALS['loggedin'] = false;
    header('Location: https://'.$_SERVER['HTTP_HOST']);
}