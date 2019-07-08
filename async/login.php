<?php

require_once ('/home/mwepf1gm/www/biblewiki.one/config/db_biblewiki_users.php');
require_once dirname(__FILE__) . "/../lib/db.class.php";

//get content of the input
$jsonTx = json_decode(file_get_contents("php://input"));


if($jsonTx->action != ""){
    try {
        $function = $jsonTx->action;
        echo $function($jsonTx->data, $userID);
        exit;
    }
    catch(Exception $e){
        $ret = array('error' => 'Action not available');
        echo json_encode($ret);
        exit;
    }
    exit;
}
else {
    $ret = array('error' => 'Action not available');
    echo json_encode($ret);
    exit();
}

function CheckLoginWeb($data){
    try {
        $_db = new db(USER_DB_URL,USER_DB_USER,USER_DB_PW,USER_DB);
        $stmt = $_db->getDB()->stmt_init();
        
        $stmt = $_db->prepare("SELECT
        ".USER_DB.".users.user_password
        FROM ".USER_DB.".users 
        WHERE ".USER_DB.".users.user_username = ?
        GROUP BY ".USER_DB.".users.user_ID;"
        );

        $stmt->bind_param("s", $data->benutzername);

        $stmt->execute();
        return json_encode(array('users' => db::getTableAsArray($stmt)));
    }
    catch(Exception $e){
        return json_encode(array('error' => $e->getMessage()));
    }
}
?>