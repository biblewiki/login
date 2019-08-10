<?php
require ('async/settings.php');
session_start();
echo $_SESSION["loggedin"] . '<br>';
echo $_COOKIE["LOGGEDIN"]. '<br>';
echo $_SESSION["id"] . '<br>';
echo $_COOKIE["ID"]. '<br>';
echo $_SESSION["firstname"] . '<br>';
echo $_COOKIE["FIRSTNAME"]. '<br>';
echo $_SESSION["lastname"] . '<br>';
echo $_COOKIE["LASTNAME"]. '<br>';
echo $_SESSION["level"] . '<br>';
echo $_COOKIE["LEVEL"]. '<br>';
echo $_SESSION["picture"] . '<br>';
echo $_COOKIE["PICTURE"]. '<br>';
echo $_SESSION["test"] . '<br>';

echo $_COOKIE['PASSWORD_TOKEN'] . '<br>';
echo $_COOKIE['PASSWORD_USER'] . '<br>';
echo $_COOKIE['TOKEN_VALID'] . '<br>';

echo $_COOKIE['ACCEPT_COOKIES'] . '<br>';

echo (".".HOST_DOMAIN) . '<br>';

echo hash(sha256, $_SESSION["loggedin"].$_SESSION["id"].$_SESSION["firstname"].$_SESSION["lastname"].$_SESSION["level"].$_SESSION["picture"]) . '<br>';
echo hash(sha256, $_COOKIE["LOGGEDIN"].$_COOKIE["ID"].$_COOKIE["FIRSTNAME"].$_COOKIE["LASTNAME"].$_COOKIE["LEVEL"].$_COOKIE["PICTURE"]) . '<br>';


?>