<?php
// User Datenbank Logindaten einbinden
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
//require_once($homedir . '/config/biblewiki/db_biblewiki_users.php');

// PHPMailer Pfad definieren
$PHPMailerDir = $homedir . '/www/biblewiki.one/mail/PHPMailer';

require $PHPMailerDir . '/src/Exception.php';
require $PHPMailerDir . '/src/PHPMailer.php';
require $PHPMailerDir . '/src/SMTP.php';
// Mail Klasse einbinden
require_once dirname(__FILE__) . "/lib/mail.class.php";

$mail = new mail();

$mail->set_to_email('jk@joelkohler.ch');
$mail->set_to_name('JK');
$mail->set_subject('Testeritest');
$mail->set_body('<strong>Iu</strong> Test');
echo $mail->send_mail();

