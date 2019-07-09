<?php
// AJAX Input decodieren
$jsonTx = file_get_contents("php://input");
var_dump($jsonTx);
// Überprüfen ob eine Action gefordert wird
if($jsonTx->action != ""){
    try {
        $function = $jsonTx->action;
        echo $function($jsonTx->data, $userID); // Funktion ausführen
        exit;
    }
    catch(Exception $e){
        $ret = array('error' => 'Action not available');
        echo json_encode($ret);
        exit;
    }
    exit;
}
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
    <!------ Include the above in your HEAD tag ---------->
    
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
    <!-- Include the above in your HEAD tag -->
    
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
                    
                        <p><span class="fa fa-user"></span><input id="benutzername" type="text"  Placeholder="Benutzername" disabled></p>
                        <p><span class="fa fa-user"></span><input id="firstname" type="text"  Placeholder="Vorname" required></p>
                        <p><span class="fa fa-user"></span><input id="lastname" type="text"  Placeholder="Nachname" required></p>
                        <p><span class="fa fa-envelope "></span><input id="email" type="email"  Placeholder="Email" required></p>
                        <p><span class="fa fa-lock"></span><input id="passwort" type="password"  Placeholder="Passwort wiederholen" required></p>
            
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
    $(document).ready(function(){
        // Login Button Klick
        $('#login-btn').click(function(){

            var benutzername = $('#benutzername').val();
            var passwort = $('#passwort').val();
            

            var jsonTx = {
		        action : 'CheckLoginWeb',
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
				        alert(data['error']);
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