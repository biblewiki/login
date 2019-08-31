<?php
// Settings einbinden
require_once $_SERVER['DOCUMENT_ROOT'] . '/php/settings.php';

// Settings einbinden
require_once $_SERVER['DOCUMENT_ROOT'] . '/php/db_connect.php';

// Mail Klasse einbinden
require_once SCRIPT_PATH . '/php/mail.class.php';

$email_text = getEmailText();

var_dump($email_text);
/*
$userID = 1;
$token = 'doagfuzigAGFGEQ784173489179';

$str = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/lang/email_DE.json');

$json = json_decode($str, true);

$mail = new mail();

$mail->set_to_userID($userID);
$mail->set_subject($email_text["login"]["confirm_email"]["subject"]);
$mail->set_preheader($email_text["login"]["confirm_email"]["preheader"]);
$mail->set_heading($email_text["login"]["confirm_email"]["heading"]);
$mail->set_text($email_text["login"]["confirm_email"]["text"]);
$mail->set_button_text($email_text["login"]["confirm_email"]["button_text"]);
$mail->set_button_link(LOGIN_HOST . '/confirm_email.php?user=' . $userID . '&token=' . $token);
$mail->set_end_text($email_text["login"]["confirm_email"]["end_text"] . '</p><p><a href="' . LOGIN_HOST . '/confirm_email.php?user=' . $userID . '&token=' . $token . '">' . LOGIN_HOST . '/confirm_email.php?user=' . $userID . '&token=' . $token . '</a>');

$result = $mail->send_mail(); // Mail senden


echo $result;
*/
?>
<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-145575129-2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-145575129-2');
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex">
    <link rel="shortcut icon" type="image/x-icon" href="/img/favicon-512x512.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon-180x180.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">

    <title>Login | BibleWiki</title>

    <!-- Include JQUERY -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!-- Include Bootstrap 4 -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <!-- Include Font Awesome CSS und local CSS -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href="css/style.css" rel="stylesheet" />

    <!-- Inlude Toast Notifications -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="<?php echo SCRIPT_URL ?>/css/notifications.css" rel="stylesheet" />
    <script src="<?php echo SCRIPT_URL ?>/js/notifications.js"></script>

</head>

<body>

    <div class="main">
        <div class="container">
            <center>
                <div class="middle">
                    <div id="login">

                        <form action="javascript:void(0);" method="get">
                            <!-- Telegram Login Button -->
                            <script async src="https://telegram.org/js/telegram-widget.js?6" data-telegram-login="<? echo $telegramBotUsername ?>" data-size="large" data-userpic="false" data-radius="3" data-auth-url="php/tauth.php" data-request-access="write"></script>
                            <!-- Google Login Button -->
                            <a id="login-button" href="<?= 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email') . '&redirect_uri=' . urlencode(GOOGLE_REDIRECT_PATH) . '&response_type=code&client_id=' . GOOGLE_CLIENT_ID . '&access_type=online' ?>"><img class="imgGoogle" src="img/btn_google_signin_dark_normal_web.png" width="100%"></a>

                            <fieldset class="clearfix">

                                <p><span class="fa fa-user"></span><input id="benutzername" type="text" Placeholder="Benutzername" required></p>
                                <p><span class="fa fa-lock"></span><input id="passwort" type="password" Placeholder="Passwort" required></p>

                                <div>
                                    <span style="width:52%; text-align:left;  display: inline-block;"><a id="forgot_password" class="small-text" href="#">Passwort vergessen?</a></span>
                                    <span style="width:46%; text-align:right;  display: inline-block;"><input id="login-btn" type="submit" value="Login"></span>
                                </div>

                            </fieldset>
                        </form>
                    </div>
                    <!-- Zweite Spalte Logo -->
                    <div class="logo">
                        <img src="img/biblewiki_weiss.svg" height="300vw">
                    </div>
                </div>
            </center>
        </div>

    </div>

<script>
    notification('warning', 'test');
</script>