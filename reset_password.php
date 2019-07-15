<?php
// DB_Connect einbinden
require_once dirname(__FILE__) . '/async/db_connect.php';

// Settings einbinden
require_once dirname(__FILE__) . '/async/settings.php';

$user = $_GET['user'];
$token = $_GET['token'];

if ($user != '' && $token != '') {
    $result = CheckPasswordToken($user, $token);

    if ($result === 'valid') {

        session_start();

        $_SESSION["password_token"] = $token;
        $_SESSION["password_user"] = $user;
        $_SESSION["token_valid"] = $result;

        setcookie("PASSWORD_TOKEN", $token, time() + 300, '/');
        setcookie("PASSWORD_USER", $user, time() + 300, '/');
        setcookie("TOKEN_VALID", $result, time() + 300, '/');
    } else {
       // header('LOCATION: ' . LOGIN_HOST . '?password_reset=error');
        exit;
    }
} else {
    header('LOCATION: ' . LOGIN_HOST);
}

?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Passwort zurücksetzen | Biblewiki</title>

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!-- Inlude Toast Notifications -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="<?php echo EDIT_HOST ?>/css/notifications.css" rel="stylesheet" />
    <script src="<?php echo EDIT_HOST ?>/js/notifications.js"></script>

    <!-- Passwort Sicherheitscheck -->
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="js/password_strenght.js"></script>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="css/style.css" rel="stylesheet" />

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

                var user = "<?php echo $user ?>";
                var token = "<?php echo $token ?>";
                var passwort = $('#passwort').val();
                var passwort2 = $('#passwort_retype').val();
                if (pwStrength >= 40) {
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
                            url: 'async/db_connect.php',
                            dataType: 'json',
                            data: JSON.stringify(jsonTx),
                            success: function(data) {
                                if (data['error'] !== undefined) {
                                    console.log(data['error']);
                                    notification('error', data['error']);
                                } else {
                                    window.location.replace("<?php echo LOGIN_HOST ?>" + '?password_reset=success');
                                }
                            }
                        });

                    } else {
                        notification('error', 'passwords_missmatch');
                    }
                } else {
                    notification('warning', 'password_parameter');
                }
            });
        });
    </script>
</body>

</html>