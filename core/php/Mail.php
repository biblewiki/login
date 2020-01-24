<?php
declare(strict_types = 1);

require_once '../lib/PHPMailer/PHPMailer.php';
require_once '../lib/PHPMailer/Exception.php';
require_once '../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

class Mail {

    protected $mail = null;

    public function __construct($config) {
        date_default_timezone_set('Europe/Zurich');

        $this->mail = new PHPMailer;

        $this->mail->isSMTP();

        $this->mail->Host = $config['host'];

        $this->mail->Port = $config['port'];

        $this->mail->SMTPAuth = $config['smtp'];

        $this->mail->SMTPSecure = $config['certificateType'];

        $this->mail->Username = $config['smtpUser'];

        $this->mail->Password = $config['smtpPassword'];

        $this->mail->setFrom('noreply@biblewiki.one', 'BibleWiki');

        $this->mail->addReplyTo('noreply@biblewiki.one', 'BibleWiki');

        $this->mail->Subject = 'Nachricht von BibleWiki';
    }

    public function from(string $email, string $name): void {
        $this->mail->setFrom($email, $name ?: $email);
    }

    public function reply(string $email, string $name): void {
        $this->mail->addReplyTo($email, $name ?: $email);
    }

    public function to(string $email, string $name): void {
        $this->mail->addAddress($email, $name ?: $email);
    }

    public function subject(string $subject): void {
        $this->mail->Subject = $subject;
    }

    public function html(string $msg): void {
        $this->mail->msgHTML($msg);
    }

    public function body(string $msg): void {
        $this->mail->AltBody = strip_tags($msg);
    }

    public function send() {
        return $this->mail->send();
    }

    public function errorInfo() {
        return $this->mail->ErrorInfo;
    }
}