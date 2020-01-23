<?php


class TelegramLogin {

    /**
     * Login überprüfen
     * @param Db $db
     * @param array $formPacket
     * @return array
     */
    public static function checkLogin(Db $db, array $formPacket): array {

        $return = [];

        // Abfrage vorbereiten
        $st = new SqlSelector('user');
        $st->addSelectElement('user.userId');
        $st->addSelectElement('role.roleType');

        $st->addFromElement('INNER JOIN role ON role.roleId = user.roleId');

        $st->addWhereElement('telegramId = :telegramId');

        $st->addParam(':telegramId', $formPacket['id'], \PDO::PARAM_STR);
        $row = $st->execute($db, false);

        // Überprüfen ob der Benutzername existiert
        if ($row) {
            $return['success'] = true;
            $return['userId'] = $row['userId'];
            $return['roleType'] = $row['roleType'];
        } else {
            $return['success'] = false;
            $return['warnMsg'] = 'Benutzer existiert nicht';
        }

        return $return;
    }


    /**
     * Registrieren
     * @param Db $db
     * @param array $formPacket
     * @return array
     */
    public static function register(Db $db, array $formPacket): array {

        // Daten vorbereiten
        $userData['telegramId'] = $formPacket['id'];
        $userData['username'] = $formPacket['username'];
        $userData['firstName'] = $formPacket['first_name'];
        $userData['lastName'] = $formPacket['last_name'];
        $userData['profilePicture'] = $formPacket['photo_url'];

        $save = new SaveData($db, 1, 'user');
        $save->save($userData);
        $userId = $save->getPrimaryKey()->value;
        unset ($save);

        $return['success'] = !!$userId;
        $return['userId'] = $userId;

        return $return;
    }
}
