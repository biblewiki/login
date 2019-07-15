<?php
/* Namespace alias. */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class mail
{
    var $name;

    // Standartwerte

    var $fromEmail = 'no-reply@biblewiki.com';
    var $fromName = 'BibleWiki';
    var $attachment = NULL;

    ######################################################
    # SET
    ######################################################

    function set_to_email($toEmail)
    {
        $this->toEmail = $toEmail;
    }

    function set_to_name($toName)
    {
        $this->toName = $toName;
    }

    function set_from_email($fromEmail)
    {
        $this->fromEmail = $fromEmail;
    }

    function set_from_name($fromName)
    {
        $this->fromName = $fromName;
    }

    function set_subject($subject)
    {
        $this->subject = $subject;
    }

    function set_attachment($attachment)
    {
        $this->attachment = $attachment;
    }

    function set_body($body)
    {
        $this->body = $body;
    }

    ######################################################
    # GET
    ######################################################

    function get_to_email()
    {
        return $this->toEmail;
    }

    function get_to_name()
    {
        return $this->toName;
    }

    function get_from_email()
    {
        return  $this->fromEmail;
    }

    function get_from_name()
    {
        return $this->fromName;
    }

    function get_subject()
    {
        return $this->subject;
    }

    function get_attachment()
    {
        return $this->attachment;
    }

    function get_body()
    {
        return $this->body;
    }


    function send_mail()
    {
        return email($this->toEmail, $this->toName, $this->fromEmail, $this->fromName, $this->subject, $this->attachment, $this->body);
    }
}

function email($toEmail, $toName, $fromEmail, $fromName, $subject, $attachment, $body)
{
    //$userData = GetReceiverData($toUserID);

    /* Create a new PHPMailer object. Passing TRUE to the constructor enables exceptions. */
    $mail = new PHPMailer(TRUE);

    /* Open the try/catch block. */
    try {

        $mail->CharSet = 'UTF-8';

        $mail->Encoding = 'base64';

        /* Set the mail sender. */
        $mail->setFrom($fromEmail, $fromName);

        /* Set a different reply-to address. */
        //$mail->addReplyTo('vader@empire.com', 'Lord Vader');

        /* Add a recipient. */
        $mail->addAddress($toEmail, $toName);

        /* Add CC and BCC recipients */
        //$mail->addCC('admiral@empire.com', 'Fleet Admiral');
        //$mail->addBCC('luke@rebels.com', 'Luke Skywalker');


        /* Set the subject. */
        $mail->Subject = $subject;

        $mail->isHTML(TRUE);
        $mail->Body = $body;
        // $mail->AltBody = $BodyNoHtml;

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
