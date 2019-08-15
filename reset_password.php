<?php
// DB_Connect einbinden
require_once($_SERVER['DOCUMENT_ROOT'] . '/php/db_connect.php');

// Settings einbinden
require_once($_SERVER['DOCUMENT_ROOT'] . '/php/settings.php');


$userID = $_GET['user'];
$token = $_GET['token'];

// Überprüfen ob User und Token in der URL vorhanden sind
if ($userID != '' && $token != '') {

    // Überprüfen ob Token für User gültig sind
    $result = CheckPasswordToken($userID, $token);

    // Wenn Token gültig
    if ($result === 'valid') {

        GetUserData($user);

        session_start();

        $_SESSION["password_token"] = $token;
        $_SESSION["password_user"] = $userID;
        $_SESSION["token_valid"] = $result;

        setcookie("PASSWORD_TOKEN", $token, time() + 120, '/');
        setcookie("PASSWORD_USER", $userID, time() + 120, '/');
        setcookie("TOKEN_VALID", $result, time() + 120, '/');
    } else {
        header('LOCATION: ' . LOGIN_HOST . '?notif=password_reset&type=error'); // Weiterleiten nach Login mit Fehlercode
        exit;
    }
} else {
    // Weiterleiten nach Login
    header('LOCATION: ' . LOGIN_HOST);
    exit;
}

?>
<html lang="en">

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
    <link rel="shortcut icon" type="image/x-icon" href="/img/favicon-512x512.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon-180x180.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">

    <title>Passwort zurücksetzen | Biblewiki</title>


    <!-- Include JQUERY -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!-- Include Bootstrap 4 -->
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>

    <!-- Include Font Awesome CSS und local CSS -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href="css/style.css" rel="stylesheet" />

    <!-- Inlude Toast Notifications -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="<?php echo SCRIPT_URL ?>/css/notifications.css" rel="stylesheet" />
    <script src="<?php echo SCRIPT_URL ?>/js/notifications.js"></script>

    <!-- Passwort Sicherheitscheck -->
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="js/password_strenght.js"></script>

</head>

<body>

    <div class="main">
        <div class="container">
            <center>
                <div class="middle">
                    <div id="login">
                        <h3>Passwort zurücksetzen</h3>
                        <br>
                        <form action="javascript:void(0);" method="get">
                            <fieldset class="clearfix">

                                <p><span class="fa fa-lock"></span><input id="passwort" type="password" Placeholder="Passwort" required></p>
                                <div class="pwstrength_viewport_progress"></div>
                                <p><span class="fa fa-lock"></span><input id="passwort_retype" type="password" Placeholder="Passwort wiederholen" required></p>

                                <div>
                                    <span style="width:52%; text-align:left;  display: inline-block;"></span>
                                    <span style="width:46%; text-align:right;  display: inline-block;"><input id="reset-btn" type="submit" value="Zurücksetzen"></span>
                                </div>

                            </fieldset>

                            <div class="clearfix"></div>
                        </form>

                        <div class="clearfix"></div>

                    </div> <!-- end login -->
                    <div class="logo">
                        <img src="img/biblewiki_weiss.svg" height="300px">
                        <div class="clearfix"></div>
                    </div>

                </div>
            </center>
        </div>

    </div>
    <script>
        $(document).ready(function() {

            // Login Button Klick
            $('#reset-btn').click(function() {

                var user = "<?php echo $userID ?>";
                var token = "<?php echo $token ?>";
                var passwort = $('#passwort').val();
                var passwort2 = $('#passwort_retype').val();

                // Passwortsicherheit überprüfen
                if (pwStrength >= 40) {

                    // Überprüfen ob beide Passwörter identisch sind
                    if (passwort === passwort2) {

                        var jsonTx = {
                            action: 'ResetPassword',
                            data: {
                                'user': user,
                                'token': token,
                                'passwort': passwort,
                                'passwort2': passwort2
                            }
                        };

                        $.ajax({
                            type: 'POST',
                            url: 'php/db_connect.php',
                            dataType: 'json',
                            data: JSON.stringify(jsonTx),
                            success: function(data) {
                                if (data['error'] !== undefined) {
                                    console.log(data['error']);
                                    notification('error', data['error']); // Fehler ausgeben
                                }
                                // Wenn erfolgreich weiterleiten
                                else {
                                    window.location.replace("<?php echo LOGIN_HOST ?>" + '?notif=password_reset&type=' + data['success']);
                                }
                            }
                        });

                    } else {
                        notification('error', 'passwords_missmatch'); // Passwörter stimmen nicht überein
                    }
                } else {
                    notification('warning', 'password_parameter'); // Passwort zu unsicher
                }
            });
        });
    </script>
    <?php include('html/footer.html'); ?>
</body>

</html>