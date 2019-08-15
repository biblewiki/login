<?php
// Domain without https:// and Subdomain !!!
define(HOST_DOMAIN, "biblewiki.one");

// Edit page address
define(EDIT_HOST, "https://edit.joel.biblewiki.one");

// Login page address
define(LOGIN_HOST, "https://login.joel.biblewiki.one");

// Scripts URL
define(SCRIPT_URL, "https://www.joel.biblewiki.one/script");

// Scripts Path
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
define(SCRIPT_PATH, $homedir . "/www/biblewiki.one/joel/www/script");

// Google auth redirect Path
define('GOOGLE_REDIRECT_PATH', 'https://login.joel.biblewiki.one/php/gauth.php');