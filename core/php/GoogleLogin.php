<?php


class GoogleLogin {

    public static function checkLogin(Db $db, array $formPacket): array {

        $return = [];

        // Abfrage vorbereiten
        $st = new SqlSelector('user');
        $st->addSelectElement('user.userId');
        $st->addSelectElement('user.roleId');

        $st->addWhereElement('googleId = :googleId');

        $st->addParam(':googleId', $formPacket['id'], \PDO::PARAM_STR);
        $row = $st->execute($db, false);

        // Überprüfen ob der Benutzername existiert
        if ($row) {
            $return['success'] = true;
            $return['userId'] = $row['userId'];
            $return['roleId'] = $row['roleId'];
        } else {
            $return['success'] = false;
            $return['warnMsg'] = 'Benutzer existiert nicht';
        }

        return $return;
    }

    public static function register(Db $db, array $formPacket): array {

        $userData['googleId'] = $formPacket['id'];
        $userData['email'] = $formPacket['email'];
        $userData['emailState'] = $formPacket['verified_email'] ? 40 : 10; // 40 =bestätigt, 10 = unbestätigt
        $userData['firstName'] = $formPacket['given_name'];
        $userData['lastName'] = $formPacket['family_name'];
        $userData['profilePicture'] = $formPacket['picture'];

        $save = new SaveData($db, 1, 'user');
        $save->save($userData);
        $userId = $save->getPrimaryKey();
        unset ($save);

        $return['success'] = !!$userId;
        $return['userId'] = $userId;
        $return['roleId'] = 1; // Standard

        return $return;
    }
}
