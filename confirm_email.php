<?php
// DB_Connect einbinden
require_once dirname(__FILE__) . '/async/db_connect.php';

// Settings einbinden
require_once dirname(__FILE__) . '/async/settings.php';

$result = CheckEmailToken($_GET['user'], $_GET['token']);

if ($result === 'success'){
    header('LOCATION: '.LOGIN_HOST.'?email_confirmed=success');
} else {
    header('LOCATION: '.LOGIN_HOST.'?email_confirmed=error');
}