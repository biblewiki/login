<?php
// Telegram API Daten einbinden
$user = posix_getpwuid(posix_getuid());
$homedir = $user['dir'];
require_once($homedir . '/config/biblewiki/biblewiki_bottoken.php');

// DB Connect einbinden
require_once($_SERVER['DOCUMENT_ROOT'] . '/php/db_connect.php');

function checkTelegramAuthorization($auth_data)
{
  $check_hash = $auth_data['hash'];
  unset($auth_data['hash']);
  $data_check_arr = [];
  foreach ($auth_data as $key => $value) {
    $data_check_arr[] = $key . '=' . $value;
  }
  sort($data_check_arr);
  $data_check_string = implode("\n", $data_check_arr);
  $secret_key = hash('sha256', BOT_TOKEN, true);
  $hash = hash_hmac('sha256', $data_check_string, $secret_key);
  if (strcmp($hash, $check_hash) !== 0) {
    throw new Exception('Data is NOT from Telegram');
  }
  if ((time() - $auth_data['auth_date']) > 86400) {
    throw new Exception('Data is outdated');
  }
  return $auth_data;
}

try {
  $auth_data = checkTelegramAuthorization($_GET);
  $result = CheckTelegramUser($auth_data);

  if ($result === "loggedin") {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . '/php/refer.php?login=true');
  } else {
    header('Location: ' . LOGIN_HOST . '?type=error&notif=' . $result);
  }
} catch (Exception $e) {
  die($e->getMessage());
}
