<?php
// Domain without https:// and Subdomain !!!
define(HOST_DOMAIN, "biblewiki.one");

// Edit page address
define(EDIT_HOST, "https://edit.joel.biblewiki.one");

// Login page address
define(LOGIN_HOST, "https://login.joel.biblewiki.one");

// Scripts URL
define(SCRIPT_URL, "https://www.joel.biblewiki.one/sources");

// Home DIR
$user = posix_getpwuid(posix_getuid());
define(HOME_DIR, $user['dir']);

// Scripts Path
define(SCRIPT_PATH, HOME_DIR . "/www/biblewiki.one/joel/www/sources");

// Google auth redirect Path
define('GOOGLE_REDIRECT_PATH', 'https://login.joel.biblewiki.one/php/gauth.php');