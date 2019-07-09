<?php

require_once('/home/mwepf1gm/www/biblewiki.one/config/db_biblewiki_users.php');

if ($_GET['telegram']){
    if (isset($_COOKIE['tg_user'])) {
        $auth_data_json = urldecode($_COOKIE['tg_user']);
        $auth_data = json_decode($auth_data_json, true);
    
        $id_telegram = $auth_data['id'];
    
        $query = "SELECT * FROM users WHERE id_telegram='$id_telegram' LIMIT 1";

        $check_level = checkUserData($query);
    }
}

if ($_GET['web']){

    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE user_username='$username' AND user_password='$password' LIMIT 1";

    $check_level = checkUserData($query);
}

function checkUserData($query){
    $con = sql_connect();

    $result = mysqli_query($con, $query)  or die("Could not connect database " .mysqli_error($con));
    $user_data = mysqli_fetch_array($result);
  var_dump($user_data);
    if ($user_data['user_level'] > 0){

        $user_data_json = json_encode($user_data);
        setcookie('user', $user_data_json);
    
        header('Location: /login/?authorised=1');

    } else {
        header('Location: /login/?logout=1');
    }
}


?>