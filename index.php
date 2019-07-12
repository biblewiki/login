<?php
// Goolge API Daten einbinden
$user = posix_getpwuid(posix_getuid()); 
$homedir = $user['dir']; 
require_once ($homedir.'/config/biblewiki/biblewiki_bottoken.php');

$bot_username = BOT_USERNAME;
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <title>Login Biblewiki</title>

    <!-- Include Bootstrap 4 -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    
    <!-- Include JQUERY -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    
    <!--
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
-->

    <!-- Include Font Awesome CSS und local CSS -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href="css/style.css" rel="stylesheet" />

    <!-- Inlude Toast Notifications -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="/css/notifications.css" rel="stylesheet" />
    <script src="/js/notifications.js"></script>

</head>
<body>
    
<div class="main">
    <div class="container">
        <center>
            <div class="middle">
                <div id="login">


                
                <form action="javascript:void(0);" method="get">
                    <!-- Telegram Login Button -->
                    <script async src="https://telegram.org/js/telegram-widget.js?6" data-telegram-login="<? echo $bot_username ?>" data-size="large" data-userpic="false" data-radius="3" data-auth-url="async/tauth.php" data-request-access="write"></script>
                    <!-- Google Login Button -->
                    <a id="login-button" href="<?= 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . GOOGLE_CLIENT_ID . '&access_type=online' ?>"><img class="imgGoogle" src="img/btn_google_signin_dark_normal_web.png" width="100%"></a>
                    
                    <fieldset class="clearfix">

                        <p><span class="fa fa-user"></span><input id="benutzername" type="text"  Placeholder="Benutzername" required></p> 
                        <p><span class="fa fa-lock"></span><input id="passwort" type="password"  Placeholder="Passwort" required></p>
            
                        <div>
                            <span style="width:52%; text-align:left;  display: inline-block;"><a class="small-text" href="#">Passwort vergessen?</a></span>
                            <span style="width:46%; text-align:right;  display: inline-block;"><input id="login-btn" type="submit" value="Login"></span>
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
    $(document).ready(function(){
        // Login Button Klick
        $('#login-btn').click(function(){

            var benutzername = $('#benutzername').val();
            var passwort = $('#passwort').val();
            

            var jsonTx = {
		        action : 'CheckPasswordUser',
		        data : { 'benutzername' : benutzername,
                        'passwort' : passwort
		        }
	        };

	        $.ajax({
	        	type: 	'POST',
		        url: 	'async/login.php',
		        dataType: 'json',
		        data: JSON.stringify(jsonTx),
		        success: function(data){
			        if(data['error'] !== undefined){

                        notification('error', data['error']);
				        
			        }else if(data['action'] === 'Register'){
                        document.cookie = 'username = ' + JSON.stringify(benutzername);
                        window.location.replace("register.php");
                    }
			        else {
                        window.location.replace("https://edit.biblewiki.one");
			        }
		        }
	        });
        });
    });

</script>
</body>
</html>