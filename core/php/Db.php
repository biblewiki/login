<?php
declare(strict_types = 1);

/**
 * Class Db
 *
 * @package ki\kgweb\ki
 */
class Db extends \PDO {
    private $preparedParams = [];


    //--------------------------------------------------------
    // Public Functions
    //--------------------------------------------------------

    /**
     * Db constructor.
     *
     * @param $dsn
     * @param string $username
     * @param string $password
     * @param array $driver_options
     */
    public function __construct(string $dsn, string $username = "", string $password = "", array $driver_options = []) {
        parent::__construct($dsn, $username, $password, $driver_options);
    }


    /**
     * Bindet die vorbereiteten Parameter
     *
     * @param \PDOStatement $st
     */
    public function bindParams(\PDOStatement $st): void {
        foreach ($this->preparedParams as $param) {
            $st->bindParam($param[0], $param[1], $param[2], $param[3], $param[4]);
        }
    }


    /**
     * Löscht die vorbereiteten Parameter
     */
    public function clearParams(): void {
        $this->preparedParams = [];
    }


    /**
     * Überprüft ob eine Tabelle existiert
     *
     * @param $tableName
     * @return bool
     * @throws \Exception
     */
    public function hasTable(string $tableName): bool {
        if (!self::validateTableName($tableName)) {
            throw new \Exception("Ungültiger Tabellenname!");
        }
        $st = $this->query("SHOW TABLES LIKE \"$tableName\";");
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        unset($st);

        return (bool)$row;
    }


    /**
     * @return bool
     */
    public function hasTransaction(): bool {

        // Funktion ist nativ vorhanden
        return (bool)$this->inTransaction();
    }


    /**
     * Überprüft, ob ein Feld in einer Tabelle existiert
     *
     * @param $tableName
     * @param $fieldName
     * @return bool
     * @throws \Exception
     */
    public function isFieldExist(string $tableName, string $fieldName): bool {
        if (!self::validateTableName($tableName)) {
            throw new \Exception("Ungültiger Tabellenname!");
        }
        if (!self::validateFieldName($fieldName)) {
            throw new \Exception("Ungültiger Feldname!");
        }
        $st = $this->query("SHOW COLUMNS FROM `$tableName` LIKE '$fieldName';");
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        unset($st);

        return (bool)$row;
    }


    /**
     * @param string $parameter
     * @param mixed $variable
     * @param int $data_type
     * @param int|null $length
     * @param array|null $driver_options
     */
    public function prepareParam(string $parameter, $variable, int $data_type = \PDO::PARAM_STR, int $length = 0, array $driver_options = null): void {
        $this->preparedParams[] = [$parameter, $variable, $data_type, $length, $driver_options];
    }


    /**
     * @return bool
     */
    public function rollBackIfTransaction(): bool {
        if ($this->hasTransaction()) {
            return $this->rollBack();
        }

        return true;
    }


    /**
     * Überprüft die Schreibweise eines Feldnamens
     *
     * @param $fieldName
     * @return bool
     */
    public static function validateFieldName(string $fieldName): bool {
        return preg_match("/^[A-Za-z0-9_\-ÄÖÜäöü\.]+$/iu", $fieldName) === 1;
    }


    /**
     * Überprüft die Schreibweise eines Tabellennamens
     *
     * @param $tableName
     * @return bool
     */
    public static function validateTableName(string $tableName): bool {
        return preg_match("/^[A-Za-z0-9_\-]+$/i", $tableName) === 1;
    }

}