<?php
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
$PHPMailerDir = $homedir . '/www/biblewiki.one/mail/PHPMailer';

/* Namespace alias. */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* Include the Composer generated autoload.php file. */
//require 'C:\xampp\composer\vendor\autoload.php';

/* If you installed PHPMailer without Composer do this instead: */

require $PHPMailerDir . '/src/Exception.php';
require $PHPMailerDir . '/src/PHPMailer.php';
require $PHPMailerDir . '/src/SMTP.php';


/* Create a new PHPMailer object. Passing TRUE to the constructor enables exceptions. */
$mail = new PHPMailer(TRUE);

/* Open the try/catch block. */
try {
    /* Set the mail sender. */
    $mail->setFrom('no-reply@biblewiki.one', 'BibleWiki');

    /* Add a recipient. */
    $mail->addAddress('jk@joelkohler.ch', 'Emperor');

    /* Set the subject. */
    $mail->Subject = 'Force';

    $mail->isHTML(TRUE);
    $mail->Body = '<html>There is a great disturbance in the <strong>Force</strong>.</html>';
    $mail->AltBody = 'There is a great disturbance in the Force.';

    /* Finally send the mail. */
    $mail->send();
} catch (Exception $e) {
    /* PHPMailer exception. */
    echo $e->errorMessage();
} catch (\Exception $e) {
    /* PHP exception (note the backslash to select the global namespace Exception class). */
    echo $e->getMessage();
}
