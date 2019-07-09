<?php

session_start();

var_dump($_SESSION);

//header('Location: /');
session_destroy();

?>