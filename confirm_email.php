<?php
// DB_Connect einbinden
require_once dirname(__FILE__) . '/async/db_connect.php';

// Settings einbinden
require_once dirname(__FILE__) . '/async/settings.php';

// Log-Script einbinden
require_once dirname(__FILE__) . '/async/log.php';

$result = CheckEmailToken($_GET['user'], $_GET['token']);

// Wenn Token gültig war und Email bestätigt wurde
if ($result === 'success'){
    UserLog($_GET['user'], 'Password', 'Email address confirmed success');
    header('LOCATION: '.LOGIN_HOST.'?notif=email_confirmed&type=success');
    
} else {
    UserLog($_GET['user'], 'Password', 'Email address confirmed failed');
    header('LOCATION: '.LOGIN_HOST.'?notif=email_confirmed&type=error');
}