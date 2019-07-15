<?php
// Include Settings
require_once($_SERVER['DOCUMENT_ROOT'] . '/async/settings.php');

$benutzername = json_decode($_COOKIE['USERNAME']);
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Passwort vergesssen | Biblewiki</title>

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
                        <h3>Passwort vergessen</h3>
                        <br>
                        <form action="javascript:void(0);" method="get">
                            <fieldset class="clearfix">

                                <p><span class="fa fa-user"></span><input id="benutzername" type="text" Placeholder="Benutzername" value="<?php echo $benutzername ?>" <?php if (!empty($benutzername)) echo 'disabled'; ?>></p>
                                <p><span class="fa fa-envelope "></span><input id="email" type="email" Placeholder="Email" required autofocus></p>

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

                var benutzername = $('#benutzername').val();
                var email = $('#email').val();

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
                            notification('error', data['error']);
                        } else {
                            window.location.replace("<?php echo LOGIN_HOST ?>" + '?password_reset=confirm_email');
                        }
                    }
                });

            });
        });
    </script>
</body>

</html>