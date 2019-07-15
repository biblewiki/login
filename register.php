<?php
require_once('async/settings.php');

// Include Settings
require_once($_SERVER['DOCUMENT_ROOT'] . '/async/settings.php');

$benutzername = json_decode($_COOKIE['username']);
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registrieren Biblewiki</title>

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!-- Inlude Toast Notifications -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="<?php echo EDIT_HOST ?>/css/notifications.css" rel="stylesheet" />
    <script src="<?php echo EDIT_HOST ?>/js/notifications.js"></script>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="css/style.css" rel="stylesheet" />

</head>

<body>

    <div class="main">
        <div class="container">
            <center>
                <div class="middle">
                    <div id="login">

                        <form action="javascript:void(0);" method="get">
                            <fieldset class="clearfix">

                                <p><span class="fa fa-user"></span><input id="benutzername" type="text" Placeholder="Benutzername" value="<?php echo $benutzername ?>" disabled></p>
                                <p><span class="fa fa-user"></span><input id="firstname" type="text" Placeholder="Vorname" required autofocus></p>
                                <p><span class="fa fa-user"></span><input id="lastname" type="text" Placeholder="Nachname" required></p>
                                <p><span class="fa fa-envelope "></span><input id="email" type="email" Placeholder="Email" required></p>
                                <p><span class="fa fa-lock"></span><input id="passwort" type="password" Placeholder="Passwort" required></p>
                                <p><span class="fa fa-lock"></span><input id="passwort_retype" type="password" Placeholder="Passwort wiederholen" required></p>

                                <div>
                                    <span style="width:52%; text-align:left;  display: inline-block;"></span>
                                    <span style="width:46%; text-align:right;  display: inline-block;"><input id="login-btn" type="submit" value="Anmelden"></span>
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
            $('#login-btn').click(function() {

                var benutzername = $('#benutzername').val();
                var vorname = $('#firstname').val();
                var nachname = $('#lastname').val();
                var email = $('#email').val();
                var passwort = $('#passwort').val();
                var passwort2 = $('#passwort_retype').val();

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
                                console.log(data['error']);
                                //notification('error', data['error']);
                            } else {
                                window.location.replace("<?php echo LOGIN_HOST ?>" + '?login=confirm_email');
                            }
                        }
                    });
                } else {
                    notification('error', 'passwords_missmatch');
                }
            });
        });
    </script>
</body>

</html>