<?php


class PasswordLogin {

    public static function checkLogin(Db $db, array $formPacket): array {

        $return = [];

        // Abfrage vorbereiten
        $st = new SqlSelector('user');
        $st->addSelectElement('user.userId');
        $st->addSelectElement('user.roleId');
        $st->addSelectElement('user.password');
        $st->addSelectElement('user.passwordState');

        $st->addWhereElement('username = :username');

        $st->addParam(':username', $formPacket['username'], \PDO::PARAM_STR);
        $row = $st->execute($db, false);

        // Überprüfen ob der Benutzername existiert
        if ($row) {

            // Password verschlüsseln
            $password = hash('sha256', $formPacket['password']);

            // Überprüfen ob das Passwort übereinstimmt
            if ($password === $row['password']) {
                $return['success'] = true;
                $return['passwordState'] = $formPacket['passwordState'];
                $return['userId'] = $row['userId'];
                $return['roleId'] = $row['roleId'];
            } else {
                $return['success'] = false;
                $return['warnMsg'] = 'Benutzername oder Passwort ist falsch';
            }
        } else {
            $return['success'] = false;
            $return['warnMsg'] = 'Benutzername oder Passwort ist falsch';
        }

        return $return;
    }
}
