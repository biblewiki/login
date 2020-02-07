<?php
declare(strict_types = 1);

/**
 * Class SessHandler
 */
class SessHandler {

    /**
     * @var App
     */
    protected $db;

    //--------------------------------------------------------
    // Public Functions
    //--------------------------------------------------------

    /**
     * Konstruktor
     *
     * @param Db $db
     */
    public function __construct(Db $db) {
        $this->db = $db;

        // Funktionen des eigenen Handlers zuweisen
        session_set_save_handler(
            [&$this, "open"],
            [&$this, "close"],
            [&$this, "read"],
            [&$this, "write"],
            [&$this, "destroy"],
            [&$this, "gc"]
        );

        $rootDomain = '.' . $_SERVER['HTTP_HOST'];

        $currentCookieParams = session_get_cookie_params();
        $currentCookieParams['domain'] = $rootDomain;
        $currentCookieParams['httponly'] = true;
        $currentCookieParams['samesite'] = 'Strict';

        session_set_cookie_params($currentCookieParams);
    }


    /**
     * Destruktor
     */
    public function __destruct() {
        $this->closeWrite();
    }


    /**
     * Diese Funktion muss unbedingt aufgerufen werden bevor die DB geschlossen wird, sonst gibt es einen Fehler
     */
    public function closeWrite(): void {
        session_write_close();
    }


    /**
     * Löscht die redundanten Sessions eines Benutzers
     *
     * @param string $userId
     * @param string $keepSessionId
     */
    public function destroyRedundantUserSessions(string $userId, string $keepSessionId = ""): void {
        if (!$userId || $userId === "guest") {
            return;
        }

        if (!$keepSessionId) {
            $keepSessionId = session_id();
        }
        $st = $this->db->prepare("
            DELETE FROM
                `session`
            WHERE
                `userId` = :userId
                AND `sessionId` != :sessionId
        ");
        $st->bindParam(":userId", $userId, \PDO::PARAM_STR);
        $st->bindParam(":sessionId", $keepSessionId, \PDO::PARAM_STR);
        $st->execute();
        unset($st);
    }


    /**
     * Gibt die Anzahl Benutzer zu einer userId zurück
     *
     * @param $userId
     * @return int
     */
    public function getUserCount(string $userId): int {
        $maxLifetime = ini_get("session.gc_maxlifetime");

        $st = $this->db->prepare("
            SELECT
                COUNT(`sessionId`) AS `cnt`
            FROM
                `session`
            WHERE
                `userId` = :userId AND
                `lastAccess` + INTERVAL :maxLifetime SECOND > NOW()
        ");
        $st->bindParam(":userId", $userId, \PDO::PARAM_STR);
        $st->bindParam(":maxLifetime", $maxLifetime, \PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        unset($st);

        return $row ? (int)$row["cnt"] : 0;
    }


    /**
     * Gibt ein Array mit den aktuell eingeloggten Benutzern zurück
     *
     * @return array
     */
    public function getCurrentUsers(): array {
        $maxLifetime = ini_get("session.gc_maxlifetime");

        $st = $st = $this->db->prepare("
            SELECT
              `userId`,
              `lastAccess`
            FROM
              `session`
            WHERE
                `lastAccess` + INTERVAL :maxLifetime SECOND > NOW()
                AND `userId` != ''
                AND `userId` != 'guest'
            ORDER BY
                `lastAccess` DESC
        ");
        $st->bindParam(":maxLifetime", $maxLifetime, \PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
        unset($st);

        return $rows;
    }


    /**
     * Löscht den Benutzernamen aus allen Sessions
     *
     * @param $userId
     */
    public function logoutUser(string $userId): void {

        // Session
        $st = $this->db->prepare("
            DELETE FROM
                `session`
            WHERE
                `userId` = :userId
        ");
        $st->bindParam(":userId", $userId, \PDO::PARAM_STR);
        $st->execute();
        unset($st);
    }


    // ----------------------------------------
    // Handler functions
    // ----------------------------------------

    /**
     * Schliesst die Session
     *
     * @return bool
     */
    public function close(): bool {
        return true;
    }


    /**
     * Löscht die Session
     *
     * @param $sessionId
     * @return bool
     */
    public function destroy(string $sessionId): bool {
        $st = $this->db->prepare("
            DELETE FROM
                `session`
            WHERE
                `sessionId` = :sessionId
        ");
        $st->bindParam(":sessionId", $sessionId, \PDO::PARAM_STR);
        $ret = (bool)$st->execute();
        unset($st);

        return $ret;
    }


    /**
     * Garbage Collector: Wird bei jedem (session.gc_probability / session.gc_divisor) Aufruf ausgeführt
     * Standardwerte: 1 / 100 -> Also bei jedem Hundertsten Aufruf, werden die alten Session-Einträge gelöscht.
     *
     * @param int $maxLifetime
     * @return bool
     */
    public function gc(int $maxLifetime): bool {
        $st = $this->db->prepare("
            DELETE FROM
                `session`
            WHERE
                `lastAccess` + INTERVAL :maxLifetime SECOND < NOW()
        ");
        $st->bindParam(":maxLifetime", $maxLifetime, \PDO::PARAM_INT);
        $ret = (bool)$st->execute();
        unset($st);

        return $ret;
    }


    /**
     * Öffnet die Session
     *
     * @param $path
     * @param $name
     * @return bool
     */
    public function open(string $path, string $name): bool {
        return true;
    }


    /**
     * Liest die Daten aus der Session
     *
     * @param string $sessionId
     * @return string
     */
    public function read(string $sessionId): string {
        $st = $this->db->prepare("
            SELECT
                `data`
            FROM
                `session`
            WHERE
                `sessionId` = :sessionId
        ");
        $st->bindParam(":sessionId", $sessionId, \PDO::PARAM_STR);
        $st->execute();
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        unset($st);

        return $row ? $row['data'] : '';
    }


    /**
     * Schreibt die Daten in die Session
     *
     * @param string $sessionId
     * @param string $data
     * @return bool
     */
    public function write(string $sessionId, string $data): bool {

        // Variablen initialisieren
        $ret = true;

        // data de-serialisieren, damit die userId und loginType ermittelt werden kann
        $sessionArr = $this->unserialize_session($data);
        $session = empty($sessionArr["biwi"]) ? null : $sessionArr["biwi"];
        unset($sessionArr);

        // UserId & loginType ermitteln
        $userId = $session->userId ?? 0;
        $loginType = $session->loginType ?? "";

        if ($userId) {
            $st = $this->db->prepare("
                REPLACE INTO `session` (
                    `sessionId`,
                    `lastAccess`,
                    `data`,
                    `userId`,
                    `loginType`
                )
                VALUES (
                    :sessionId,
                    NOW(),
                    :data,
                    :userId,
                    :loginType
                )
            ");
            $st->bindParam(":sessionId", $sessionId, \PDO::PARAM_STR);
            $st->bindParam(":data", $data, \PDO::PARAM_STR);
            $st->bindParam(":userId", $userId, \PDO::PARAM_STR);
            $st->bindParam(":loginType", $loginType, \PDO::PARAM_STR);
            $ret = (bool)$st->execute();
            unset($st);
        }

        return $ret;
    }


    // ----------------------------------------
    // Protected functions
    // ----------------------------------------

    /**
     * @param string $data
     * @return array
     */
    protected function unserialize_session(string $data): array {
        if ($data === '') {
            return [];
        }

        // match all the session keys and offsets
        preg_match_all("/(^|;|\})([a-zA-Z0-9_]+)\|/i", $data, $matchesArray, PREG_OFFSET_CAPTURE);
        $returnArray = [];
        $lastOffset = null;
        $currentKey = "";
        foreach ($matchesArray[2] as $value) {
            $offset = $value[1];
            if ($lastOffset !== null) {
                $valueText = mb_substr($data, (int)$lastOffset, $offset - $lastOffset);
                $returnArray[$currentKey] = unserialize($valueText, ["allowed_classes" => [biwi\Session::class]]);
            }
            $currentKey = $value[0];
            $lastOffset = $offset + mb_strlen($currentKey) + 1;
        }
        $valueText = mb_substr($data, (int)$lastOffset);
        $returnArray[$currentKey] = unserialize($valueText, ["allowed_classes" => [biwi\Session::class]]);
        return $returnArray;
    }
}
