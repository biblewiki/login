<?php
// Google und Telegram API Daten einbinden
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
require_once($homedir . '/config/biblewiki/biblewiki_bottoken.php');

$telegramBotUsername = BOT_USERNAME;

// Settings einbinden
require_once($_SERVER['DOCUMENT_ROOT'] . '/php/settings.php');

// Session starten
session_start();
?>

<html lang="en">

<head>
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

    <!-- Inlude Cookie Script -->
    <script src="<?php echo SCRIPT_URL ?>/js/cookie.js"></script>

    <!-- Set Language Cookie -->
    <script>
        createCookie('LANGUAGE', 'DE', 8000);
    </script>

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

    <!-- Script -->
    <script>
        $(document).ready(function() {

            // Login Button klick
            $('#login-btn').click(function() {

                var benutzername = $('#benutzername').val();
                var passwort = $('#passwort').val();

                // Überprüfen ob Benutzername nicht leer ist
                if (benutzername != '') {

                    var jsonTx = {
                        action: 'CheckPasswordUser',
                        data: {
                            'benutzername': benutzername,
                            'passwort': passwort
                        }
                    };

                    $.ajax({
                        type: 'POST',
                        url: 'php/db_connect.php',
                        dataType: 'json',
                        data: JSON.stringify(jsonTx),
                        success: function(data) {
                            if (data['error'] !== undefined) {
                                notification('error', data['error']); // Fehler anzeigen
                            }
                            // Wenn Benutzer noch nicht registriert ist
                            else if (data['action'] === 'register') {
                                document.cookie = 'USERNAME = ' + JSON.stringify(benutzername); // Benutzername in Cookie schreiben
                                window.location.replace("<?php echo LOGIN_HOST ?>" + "/register.php"); // Auf Registrierseite weiterleiten
                            }
                            // Wenn User eingeloggt
                            else if (data['success'] !== undefined) {
                                window.location.replace('/php/refer.php?login=true'); // Weiterleiten wenn eingeloggt
                            }
                        }
                    });
                }
                // Benachrichtigung wenn kein Benutzername eingegeben wurde
                else {
                    notification('warning', 'fields_emty');
                }
            });

            // Passwort vergessen Link klick
            $('#forgot_password').click(function() {

                var benutzername = $('#benutzername').val();

                //document.cookie = 'USERNAME = ' + JSON.stringify(benutzername); // Benutzername in Cookie schreiben
                createCookie('USERNAME', 'benutzername');
                window.location.replace("<?php echo LOGIN_HOST ?>" + "/forgot_password.php"); // Auf Passwort vergessen Seite weiterleiten
            });

        });
    </script>

    <!-- Footer einbinden -->
    <?php include('html/footer.html'); ?>

</body>

</html>