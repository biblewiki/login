<?php
// User Datenbank Logindaten einbinden
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
require_once($homedir . '/config/biblewiki/db_biblewiki_users.php');

// PHPMailer Pfad definieren
$PHPMailerDir = $homedir . '/www/biblewiki.one/mail/PHPMailer';

// Datenbank Classe einbinden
require_once dirname(__FILE__) . "/../lib/db.class.php";

/* Namespace alias. */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $PHPMailerDir . '/src/Exception.php';
require $PHPMailerDir . '/src/PHPMailer.php';
require $PHPMailerDir . '/src/SMTP.php';

function email($toUserID, $from, $Subject, $BodyHtml, $BodyNoHtml, $attachment = NULL)
{
    $userData = GetReceiverData($toUserID);

    if ($from === '' || $from === NULL) {
        $from = 'no-reply@biblewiki.one';
    }

    /* Create a new PHPMailer object. Passing TRUE to the constructor enables exceptions. */
    $mail = new PHPMailer(TRUE);

    /* Open the try/catch block. */
    try {

        $mail->CharSet = 'UTF-8';

        $mail->Encoding = 'base64';
        
        /* Set the mail sender. */
        $mail->setFrom($from, 'BibleWiki');

        /* Set a different reply-to address. */
        //$mail->addReplyTo('vader@empire.com', 'Lord Vader');

        /* Add a recipient. */
        $mail->addAddress($userData[0]['user_email'], $userData[0]['user_firstname'] . ' ' . $userData[0]['user_lastname']);

        /* Add CC and BCC recipients */
        //$mail->addCC('admiral@empire.com', 'Fleet Admiral');
        //$mail->addBCC('luke@rebels.com', 'Luke Skywalker');


        /* Set the subject. */
        $mail->Subject = $Subject;

        $mail->isHTML(TRUE);
        $mail->Body = $BodyHtml;
        $mail->AltBody = $BodyNoHtml;

        if (isset($attachment)) {
            $mail->addAttachment($attachment);
        }


        /* SMTP parameters. */

        /* Tells PHPMailer to use SMTP. */
        $mail->isSMTP();

        /* SMTP server address. */
        $mail->Host = 'asmtp.mail.hostpoint.ch';

        /* Use SMTP authentication. */
        $mail->SMTPAuth = TRUE;

        /* Set the encryption system. */
        $mail->SMTPSecure = 'tls';

        /* SMTP authentication username. */
        $mail->Username = 'webmaster@biblewiki.one';

        /* SMTP authentication password. */
        $mail->Password = 'yQq4KE8n';

        /* Set the SMTP port. */
        $mail->Port = 587;



        /* Finally send the mail. */
        $mail->send();

        return('success');
    } catch (Exception $e) {
        /* PHPMailer exception. */
        return $e->errorMessage();
    } catch (\Exception $e) {
        /* PHP exception (note the backslash to select the global namespace Exception class). */
        return $e->getMessage();
    }
}

// Benutzerinfos abrufen
function GetReceiverData($userID)
{
    try {
        // Datenbankverbindung herstellen
        $_db = new db(USER_DB_URL, USER_DB_USER, USER_DB_PW, USER_DB);
        $stmt = $_db->getDB()->stmt_init();

        // Select definieren
        $stmt = $_db->prepare(
            "SELECT
        " . USER_DB . ".users.user_firstname,
        " . USER_DB . ".users.user_lastname,
        " . USER_DB . ".users.user_email,
        " . USER_DB . ".users.user_picture
        FROM " . USER_DB . ".users 
        WHERE " . USER_DB . ".users.user_ID = ?;"
        );

        $stmt->bind_param("i", $userID);

        $stmt->execute();

        $array = db::getTableAsArray($stmt);

        return $array;
    } catch (Exception $e) {
        return $e->getMessage();
        exit;
    }
}

//email('1', NULL, 'Test', $html, '$BodyNoHtml');
