<?php

// Goolge API Daten einbinden
require_once ('/home/mwepf1gm/www/biblewiki.one/config/biblewiki_bottoken.php');
// Google API einbinden
require_once ('../lib/google-login-api.php');
// Login Check einbinden
require_once ('login.php');


// Google passes a parameter 'code' in the Redirect Url
if(isset($_GET['code'])) {
	try {
		$gapi = new GoogleLoginApi();
		
		// Get the access token 
		$data = $gapi->GetAccessToken(GOOGLE_CLIENT_ID, CLIENT_REDIRECT_URL, GOOGLE_CLIENT_SECRET, $_GET['code']);
		
		// Get user information
		$user_info = $gapi->GetUserProfileInfo($data['access_token']);

		$result = CheckGoogleUser($user_info);
var_dump($result);
		if ($result === "Session started"){
			header('Location: https://edit.josua.biblewiki.one');
		}
	}
	catch(Exception $e) {
		echo $e->getMessage();
		exit();
	}

	
}
?>