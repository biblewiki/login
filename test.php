<?php 
session_start();
echo $_SESSION["password_token"].'<br>';
echo $_SESSION["password_user"].'<br>';
echo $_SESSION["token_valid"].'<br>';

echo $_COOKIE['PASSWORD_TOKEN'].'<br>';
echo $_COOKIE['PASSWORD_USER'].'<br>';
echo $_COOKIE['TOKEN_VALID'].'<br>';