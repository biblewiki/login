<?php
// // User Datenbank Logindaten einbinden
$user = posix_getpwuid(posix_getuid());
require_once $user['dir'] . '/config/biblewiki/db_biblewiki_users.php';

// Datenbank Klasse einbinden
require_once $user['dir'] . '/www/biblewiki.one/joel/www/script/php/db.class.php';

require_once './clean_userLog.php';


echo cleanUserLog();


?>