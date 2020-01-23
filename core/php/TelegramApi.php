<?php


class TelegramApi {

    function checkTelegramAuthorization(string $token, array $auth_data) {

        // Überprüfen ob die Daten nicht älter als 60s sind
        if ((time() - $auth_data['auth_date']) > 60) {
            $return['success'] = false;
            $return['errorMsg'] = 'Daten veraltet. Versuchen Sie sich erneut anzumelden.';
        } else {

            // Hash auslesen und aus Array löschen
            $check_hash = $auth_data['hash'];
            unset($auth_data['hash']);

            // Alle Daten neu formatiert in Array schreiben
            $data_check_arr = [];
            foreach ($auth_data as $key => $value) {
              $data_check_arr[] = $key . '=' . $value;
            }

            // Array nach ABC sortieren
            sort($data_check_arr);

            // Array mit \n getrennt in String schreiben
            $data_check_string = implode("\n", $data_check_arr);

            // Token hashen
            $secret_key = hash('sha256', $token, true);
            $hash = hash_hmac('sha256', $data_check_string, $secret_key);

            // Überprüfen ob die Hashs übereinstimmen
            if (strcmp($hash, $check_hash) === 0) {
                $return['success'] = true;
            } else {
                $return['success'] = false;
                $return['errorMsg'] = 'Login fehlgeschlagen. Versuchen Sie sich erneut anzumelden.';
            }
        }

        return $return;
    }
}