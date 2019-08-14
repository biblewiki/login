<?php
// Goolge API Daten einbinden
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
require_once($homedir . '/config/biblewiki/biblewiki_bottoken.php');
// Google API einbinden
require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/google-login-api.php');

// DB Connect einbinden
require_once($_SERVER['DOCUMENT_ROOT'] . '/php/db_connect.php');


// Google passes a parameter 'code' in the Redirect Url
if (isset($_GET['code'])) {
	try {
		$gapi = new GoogleLoginApi();

		// Get the access token 
		$data = $gapi->GetAccessToken(GOOGLE_CLIENT_ID, GOOGLE_REDIRECT_PATH, GOOGLE_CLIENT_SECRET, $_GET['code']);

		// Get user information
		$user_info = $gapi->GetUserProfileInfo($data['access_token']);

		// User erstellen oder Daten holen
		$result = CheckGoogleUser($user_info);

		// Wenn die Session gestartet wurde, weiterleiten
		if ($result === "loggedin") {
			header('Location: https://' . $_SERVER['HTTP_HOST'] . '/php/refer.php?login=true');
		} else {
			header('Location: ../');
		}
	} catch (Exception $e) {
		echo $e->getMessage();
		exit();
	}
}
