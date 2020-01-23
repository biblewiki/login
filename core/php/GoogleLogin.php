<?php


class GoogleLogin {

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

        $st->addWhereElement('googleId = :googleId');

        $st->addParam(':googleId', $formPacket['id'], \PDO::PARAM_STR);
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

        // Überprüfen ob Email schon registriert
        $st = new SqlSelector('user');
        $st->addSelectElement('COUNT(email) AS count');
        $st->addWhereElement('email = :email AND email IS NOT NULL');
        $st->addParam(':email', $formPacket['email']);
        $row = $st->execute($db);
        unset ($st);

        // Überprüfen ob Email schon existiert
        if ($row['count'] && $formPacket['email']) {
            $return['errorMsg'] = 'Emailadresse ist bereits registriert';

        // Neuer User hinzufügen
        } else {
            // Daten vorbereiten
            $userData['googleId'] = $formPacket['id'];
            $userData['email'] = $formPacket['email'];
            $userData['emailState'] = $formPacket['verified_email'] ? 40 : 10; // 40 =bestätigt, 10 = unbestätigt
            $userData['firstName'] = $formPacket['given_name'];
            $userData['lastName'] = $formPacket['family_name'];
            $userData['profilePicture'] = $formPacket['picture'];

            $save = new SaveData($db, 1, 'user');
            $save->save($userData);
            $userId = $save->getPrimaryKey()->value;
            unset ($save);

            $return['success'] = !!$userId;
            $return['userId'] = $userId;
        }

        return $return;
    }
}
