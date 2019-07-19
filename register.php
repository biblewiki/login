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
    <link rel="shortcut icon" type="image/x-icon" href="/img/favicon-512x512.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon-180x180.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">

    <title>Registrieren | Biblewiki</title>

    <!-- Include JQUERY -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!-- Include Bootstrap 4 -->
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>

    <!-- Inlude Toast Notifications -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="<?php echo EDIT_HOST ?>/css/notifications.css" rel="stylesheet" />
    <script src="<?php echo EDIT_HOST ?>/js/notifications.js"></script>

    <!-- Passwort Sicherheitscheck -->
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="js/password_strenght.js"></script>

    <!-- Include Font Awesome CSS und local CSS -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="css/style.css" rel="stylesheet" />

</head>

<body>

    <div class="main">
        <div class="container">
            <center>
                <div class="middle">
                    <div class="row" id="login">
                        <form action="javascript:void(0);" method="get">
                            <fieldset class="clearfix">

                                <p><span class="fa fa-user"></span><input id="benutzername" type="text" Placeholder="Benutzername" value="<?php echo $benutzername ?>" disabled></p>
                                <p><span class="fa fa-user"></span><input id="firstname" type="text" Placeholder="Vorname" required autofocus></p>
                                <p><span class="fa fa-user"></span><input id="lastname" type="text" Placeholder="Nachname" required></p>
                                <p><span class="fa fa-envelope "></span><input id="email" type="email" Placeholder="Email" required></p>
                                <p><span class="fa fa-lock"></span><input id="passwort" type="password" Placeholder="Passwort" required></p>
                                <div class="pwstrength_viewport_progress"></div>
                                <p><span class="fa fa-lock"></span><input id="passwort_retype" type="password" Placeholder="Passwort wiederholen" required></p>

                                <div>
                                    <span style="width:52%; text-align:left;  display: inline-block;"></span>
                                    <span style="width:46%; text-align:right;  display: inline-block;"><input id="register-btn" type="submit" value="Anmelden"></span>
                                </div>

                            </fieldset>
                        </form>
                    </div>
                    <!-- Zweite Spalte Logo -->
                    <div class="logo">
                        <img src="img/biblewiki_weiss.svg" height="300vw">
                        <div class="clearfix"></div>
                    </div>
                </div>
            </center>
        </div>

    </div>

    <!-- Script -->
    <script>
        $(document).ready(function() {

            // Registrier Button klick
            $('#register-btn').click(function() {

                var benutzername = $('#benutzername').val();
                var vorname = $('#firstname').val();
                var nachname = $('#lastname').val();
                var email = $('#email').val();
                var passwort = $('#passwort').val();
                var passwort2 = $('#passwort_retype').val();

                // Überprüfen ob alle benötigten Felder ausgefüllt sind
                if (benutzername != '' && vorname != '' && nachname != '' && email != '') {

                    // Passwortsicherheit überprüfen
                    if (pwStrength >= 40) {

                        // Überprüfen ob beide Passwörter identisch sind
                        if (passwort === passwort2) {

                            var jsonTx = {
                                action: 'AddPasswordUser',
                                data: {
                                    'benutzername': benutzername,
                                    'vorname': vorname,
                                    'nachname': nachname,
                                    'email': email,
                                    'passwort': passwort,
                                    'passwort2': passwort2
                                }
                            };

                            $.ajax({
                                type: 'POST',
                                url: 'async/db_connect.php',
                                dataType: 'json',
                                data: JSON.stringify(jsonTx),
                                success: function(data) {
                                    if (data['error'] !== undefined) {
                                        notification('error', data['error']); // Fehler anzeigen
                                    } else {
                                        window.location.replace("<?php echo LOGIN_HOST ?>" + '?login=' + data['success']); // Nach Login weiterleiten
                                    }
                                }
                            });

                        } else {
                            notification('error', 'passwords_missmatch'); // Passwörter stimmen nicht überein
                        }
                    } else {
                        notification('warning', 'password_parameter'); // Passwort zu unsicher
                    }
                } else {
                    notification('warning', 'fields_emty'); // Nicht alle Felder sind ausgefüllt
                }


            });
        });
    </script>
    <?php include('html/footer.html'); ?>
</body>

</html>