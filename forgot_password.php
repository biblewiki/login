<?php
// Settings einbinden
require_once($_SERVER['DOCUMENT_ROOT'] . '/async/settings.php');

// Benutzername aus Cookie lesen
$benutzername = json_decode($_COOKIE['USERNAME']);
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" media="screen" href="/css/main.css">
    <link rel="shortcut icon" type="image/x-icon" href="/img/favicon-512x512.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon-180x180.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">
    
    <title>Passwort vergesssen | Biblewiki</title>

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
    <link href="<?php echo EDIT_HOST ?>/css/notifications.css" rel="stylesheet" />
    <script src="<?php echo EDIT_HOST ?>/js/notifications.js"></script>

</head>

<body>

    <div class="main">
        <div class="container">
            <center>
                <div class="middle">
                    <div id="login">
                        <h3>Passwort vergessen</h3>
                        <br>
                        <form action="javascript:void(0);" method="get">
                            <fieldset class="clearfix">

                                <p><span class="fa fa-user"></span><input id="benutzername" type="text" Placeholder="Benutzername" value="<?php echo $benutzername ?>" <?php echo (!empty($benutzername) ? 'disabled' : 'autofocus'); ?>></p>
                                <p><span class="fa fa-envelope "></span><input id="email" type="email" Placeholder="Email" required <?php if (!empty($benutzername)) echo 'autofocus'; ?>></p>

                                <div>
                                    <span style="width:52%; text-align:left;  display: inline-block;"></span>
                                    <span style="width:46%; text-align:right;  display: inline-block;"><input id="reset-btn" type="submit" value="Zurücksetzen"></span>
                                </div>

                            </fieldset>
                        </form>
                    </div>
                    <!-- Zweite Spalte Logo -->
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
            // Reset Button Klick
            $('#reset-btn').click(function() {

                var benutzername = $('#benutzername').val();
                var email = $('#email').val();

                // Überprüfen ob Benutzername und Email nicht leer ist
                if (benutzername != '' && email != '') {

                    var jsonTx = {
                        action: 'RequestResetPassword',
                        data: {
                            'benutzername': benutzername,
                            'email': email
                        }
                    };

                    $.ajax({
                        type: 'POST',
                        url: 'async/db_connect.php',
                        dataType: 'json',
                        data: JSON.stringify(jsonTx),
                        success: function(data) {
                            if (data['error'] !== undefined) {
                                notification('error', data['error']); // Fehler ausgeben
                            }
                            // Wenn erfolgreich weiterleiten
                            else {
                                window.location.replace("<?php echo LOGIN_HOST ?>" + '?password_reset=' + data['success']);
                            }
                        }
                    });
                } else {
                    notification('warning', 'fields_emty'); // Nicht alle Felder sind ausgefüllt
                }
            });
        });
    </script>
    <?php include('html/footer.html'); ?>
</body>

</html>