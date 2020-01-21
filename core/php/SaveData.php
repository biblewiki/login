<?php


class SaveData {
    /**
     * @var Db
     */
    protected $db;
    /**
     * @var string
     */
    protected $userId;
    /**
     * @var string
     */
    protected $tableName;
    /**
     * @var array
     */
    protected $rows;
    /**
     * @var array
     */
    protected $primaryKeys;
    /**
     * @var bool
     */
    protected $hasChangeInfoFields;
    /**
     * @var int
     */
    protected $version;



    // -------------------------------------------------------
    // Public Methods
    // -------------------------------------------------------
    public function __construct(Db $db, int $userId, string $tableName) {
        $this->db = $db;
        $this->userId = $userId;
        $this->tableName = $tableName;
        $this->rows = [];
        $this->primaryKeys = [];
        $this->hasChangeInfoFields = false;
    }


    public function getPrimaryKey(): ?\stdClass {
        return $this->primaryKeys[0] ?? null;
    }


    public function getPrimaryKeys(): array {
        return $this->primaryKeys;
    }


    public function getVersion(): int {
        return $this->version ?: 0;
    }


    public function save(array &$formPacket): void {

        // Felder und deren Infos aus DB entnehmen
        $st = $this->db->query('SHOW COLUMNS FROM `'.$this->tableName . '`');
        $this->rows = $st->fetchAll(\PDO::FETCH_ASSOC);

        $oldValueExist = true;
        $forceInsert = false;
        $restoreMode = false;

        // Primary Key und Infos ermitteln
        foreach ($this->rows as $row) {
            if ($row['Key'] === 'PRI') {
                $field = $this->getField($row, $formPacket);
                $this->primaryKeys[] = $field;

                if (!$field->value) {
                    if($field->name === 'version') {
                        $key = $this->getNextVersion($formPacket);
                    } elseif (!$field->isAutoIncrement) {
                        $key = $this->getNextPrimaryKey($field, $formPacket);
                    }
                    $field->value = $key;
                    $formPacket[$field->name] = $key;
                } elseif($field->name === 'version') {
                    $this->version = $field->value;
                }
                if (!$field->oldValue) {
                    $oldValueExist = false;
                }
                if ($field->forceInsert){
                    $forceInsert = true;
                }
                if ($field->restoreMode){
                    $restoreMode = true;
                }
            }
        }

        // Ermitteln ob die changeInfos-Felder existieren
        $this->hasChangeInfoFields = $this->hasField('createId');


        // Überprüfen, ob der Datensatz in der Zwischenzeit von einem anderen
        // Benutzer geändert oder gelöscht wurde
        if ($this->hasChangeInfoFields && !$restoreMode && $oldValueExist) {
            $error = $this->checkIsChangedByOtherUser($formPacket);
            if ($error) {
                throw new ExceptionNotice($error);
            }
        }

        // Erstell- und Änderungsinformationen im FormPacket aktualisieren
        if ($this->hasChangeInfoFields) {
            $this->updateChangeInfos($formPacket);
        }

        // Bestehenden Datensatz aktualisieren
        if ($oldValueExist && !$forceInsert) {
            $this->updateRecord($formPacket);
        } else {
            // Neuen Datensatz erstellen
            $this->addNewRecord($formPacket);
        }
    }


    // -------------------------------------------------------
    // Private Methods
    // -------------------------------------------------------
    private function addNewRecord(array &$formPacket): void {
        $this->db->clearParams();

        $fields = '';
        $values = '';

        foreach ($this->rows as $row) {
            $fld = $this->getField($row, $formPacket);

            // Auto-Increment-Felder und nicht übermittelte Felder nie aktualisieren
            if ((!$fld->isAutoIncrement || $fld->forceInsert) && $fld->isSet) {

                if ($fields) {
                    $fields.= ', ';
                }
                if ($values) {
                    $values.= ', ';
                }

                $fields .= '`' . $fld->name . '`';
                $values .= ':'.$fld->name;

                $this->db->prepareParam(':'.$fld->name, $fld->value, $fld->type);
            }
        }

        $st = $this->db->prepare('
            INSERT INTO `' . $this->tableName . '` (
              ' . $fields . '
            )
            VALUES (
              ' . $values . '
            )
        ');
        $this->db->bindParams($st);
        $st->execute();

        // Wenn es mehrere Primary Key gibt steht überall schon ein Wert drinn
        // Nur bei einem Primary Key mit Autoincrement wird die lastInsertId hinzugefügt
        foreach ($this->primaryKeys as $primaryKey) {
            if ($primaryKey->value === null) {
                $primaryKey->value = $this->db->lastInsertId();
            }
        }
    }


    private function checkIsChangedByOtherUser(array &$formPacket): ?string {
        $this->db->clearParams();
        $error = null;

        $where = '';
        foreach ($this->primaryKeys as $primaryKey) {
            if ($where) {
                $where .= ' AND ';
            }

            // Hier muss die oldValue genommen werden, da sie vielleicht geändert wurde
            $this->db->prepareParam(':pk_'.$primaryKey->name, $primaryKey->oldValue, $primaryKey->type);
            $where .= '`' . $primaryKey->name . '` = :pk_'.$primaryKey->name;
        }

        // Falls eine Transaktion am laufen ist, sperren wir den Datensatz
        // mit 'FOR UPDATE'. Damit wird eine parallele Veränderung durch
        // eine andere Transaktion verhindert.
        $forUpdate = '';
        if ($this->db->hasTransaction()) {
            $forUpdate = 'FOR UPDATE';
        }

        // Datensatz laden
        $st = $this->db->prepare('
            SELECT changeDate, changeId
            FROM `' . $this->tableName . '`
            WHERE ' . $where . '
            ' . $forUpdate . '
        ');

        $this->db->bindParams($st);
        $st->execute();
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        unset ($st);

        if ($row) {
            $currentTimestamp = strtotime($row['changeDate']);
            $currentId = $row['changeId'];
            $timestampOnOpen = strtotime($formPacket['openTS']);

            // Wurde der Datensatz geändert?
            if ($currentTimestamp > $timestampOnOpen) {
                $error = 'Der Datensatz wurde in der Zwischenzeit durch den Benutzer ' .
                htmlspecialchars($currentId) . ' geändert. Bitte brechen Sie die Bearbeitung dieses ' .
                    'Datensatzes ab und nehmen Sie die Änderungen erneut vor.';
            }
        } else {
            // Datensatz nicht mehr vorhanden
            $error = 'Der Datensatz wurde durch einen anderen Benutzer gelöscht!';
        }
        return $error;
    }


    private function getNextPrimaryKey(\stdClass $primaryKey, array &$formPacket): int {

        // Höchsten Primarykey auslesen
        $st = $this->db->query('SELECT MAX(' . $primaryKey->name . ') AS maxKey FROM `' . $this->tableName . '`');
        $key = $st->fetchAll(\PDO::FETCH_ASSOC);

        return $key[0]['maxKey'] + 1;
    }


    private function getNextVersion(array &$formPacket): int {
        $where = '';

        foreach ($this->primaryKeys as $primaryKey) {
            if ($primaryKey->name !== 'version' && $primaryKey->value){
                $where .= $where ? ' AND ' : ' WHERE ';
                $where .= $primaryKey->name . ' = ' . $primaryKey->value;
            }
        }

        if ($where) {
            // Höchsten Primarykey auslesen
            $st = $this->db->query('SELECT MAX(version) FROM `' . $this->tableName . '`' . $where);
            $version = $st->fetchAll(\PDO::FETCH_ASSOC);

            $this->version = $version[0]['MAX(version)'] + 1;

        } else {
            $this->version = 1;
        }

        return $this->version;
    }


    private function getField(array $row, array &$formPacket): \stdClass {
        $ret = new \stdClass();

        $ret->restoreMode = false;
        $ret->forceInsert = false;
        if (\array_key_exists('restore_operation', $formPacket)) {

            // IS = Insert Same ID, IN = Insert New ID, U = Update
            $ret->restoreMode = \in_array($formPacket['restore_operation'], ['IS', 'IN', 'U'], true);

            // Bei Insert Same und Insert New wird immer ein Insert gemacht
            if ($formPacket['restore_operation'] === 'IS' || $formPacket['restore_operation'] === 'IN') {
                $ret->forceInsert = true;
            }

            // Bei einem Update die ID ins OldVal Schreiben
            if ($formPacket['restore_operation'] === 'U' || $formPacket['restore_operation'] === 'IS') {
                if (\array_key_exists("oldVal_" . $row['Field'], $formPacket)) {
                    $formPacket["oldVal_" . $row['Field']] = $formPacket['kiRestore_xId'];
                }
            }

            // Die ID als Wert ins Feld Schreiben
            if ($row['Key'] === 'PRI' && $formPacket['restore_operation'] === 'IS') {
                $formPacket[$row['Field']] = $formPacket['kiRestore_xId'];
            }
        }

        $ret->name = $row['Field'];
        $ret->isPrimaryKey = ($row['Key'] === 'PRI');
        $ret->isAutoIncrement = (mb_strpos($row['Extra'], "auto_increment") !==false);
        $ret->isSet = \array_key_exists($row['Field'], $formPacket) ? 1 : 0;
        $ret->oldValue = $formPacket["oldVal_" . $row['Field']] ?? null;

        // Datentyp ermitteln
        $val = $formPacket[$row['Field']];
        $type = $row['Type'];
        $type = explode('(', $type);
        $type = $type[0];
        switch ($type) {
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
            case 'tinyint unsigned':
            case 'smallint unsigned':
            case 'mediumint unsigned':
            case 'int unsigned':
            case 'bigint unsigned':

                // Evtl. vorhandene Tausendertrennzeichen entfernen
                if (\is_string($val)) {
                    $thousandSeparators = [" ", "'", ","];
                    $val = str_replace($thousandSeparators, "", $val);

                // boolean in zahlen
                } else if (\is_bool($val)) {
                    $val = $val ? 1 : 0;
                }

                // Damit mit dem Kombinationsfeld ki_yesno drei verschiedene
                // Werte (0, 1 und null) möglich sind, werden die Werte für
                // die GUI abgeändert ('Yes', 'No' und null).
                switch ($val."") {
                    case 'Yes': $val=1; break;
                    case 'No': $val=0; break;
                }

                if ($val."" != ((int)$val)."") {
                    $val = null;
                }
                $ret->value = $val;
                $ret->type = ($val === null && $row['Null'] === 'YES') ? \PDO::PARAM_NULL : \PDO::PARAM_INT;
                break;

            case 'decimal':
            case 'float':
            case 'double':
            case 'decimal unsigned':
            case 'float unsigned':
            case 'double unsigned':

                // Evtl. vorhandene Tausendertrennzeichen entfernen
                $thousandSeparators = [" ", "'", ","];
                $val = str_replace($thousandSeparators, "", $val);

                if ($val."" === "") {
                    $val = null;
                }
                $ret->value = $val;
                $ret->type = ($val === null && $row['Null'] === 'YES') ? \PDO::PARAM_NULL : \PDO::PARAM_STR;
                break;

            case 'date':
                // Datum In SQL-Datum umwandeln
                $ret->value = $val ? date('Y-m-d', strtotime($val)) : null;
                $ret->type = \PDO::PARAM_STR;
                break;

            case 'datetime':
                // Datum und Uhrzeit In SQL-Datum-Uhrzeit umwandeln
                $ret->value = $val ? date('Y-m-d H:i:s', strtotime($val)) : null;
                $ret->type = \PDO::PARAM_STR;
                break;

            case 'time':
                // Uhrzeit In SQL-Uhrzeit umwandeln
                $ret->value = $val ? date('H:i:s', strtotime($val)) : null;
                $ret->type = \PDO::PARAM_STR;
                break;

            default:
                $ret->value = trim(str_replace("\r\n", "\n", $val . ''));

                // Falls im Feld Nullwerte zugelassen sind: leerstrings als NULL speichern
                // NULL-Werte sind nötig um einen eindeutigen Index haben zu können,
                // der leere Felder ignoriert. NULL-Werte werden vom index ignoriert, Leerstrings nicht.
                if ($row['Null'] === 'YES' && $ret->value === '') {
                    $ret->value = NULL;
                    $ret->type = \PDO::PARAM_NULL;
                } else {
                    $ret->type = \PDO::PARAM_STR;
                }
                break;
        }
        return $ret;
    }


    private function hasField(string $fieldName): bool {
        foreach ($this->rows as $row) {
            if ($row['Field'] === $fieldName) {
                return true;
            }
        }
        return false;
    }


    private function updateChangeInfos(array &$formPacket): void {
        $timestamp = date('Y-m-d H:i:s');

        $oldValueExist = true;
        foreach ($this->primaryKeys as $primaryKey) {
            if (!$primaryKey->oldValue) {
                $oldValueExist = false;
                break;
            }
        }

        // Bei neuen Datensätzen auch die Erstellinfos schreiben
        if (!$oldValueExist){
            if ($this->hasField('createId')) {
                $formPacket['createId'] = $this->userId;
            }
            if ($this->hasField('createDate')) {
                $formPacket['createDate'] = $timestamp;
            }
        }

        // Die Änderungsinfos immer schreiben
        if ($this->hasField('changeId')) {
            $formPacket['changeId'] = $this->userId;
        }
        if ($this->hasField('changeDate')) {
            $formPacket['changeDate'] = $timestamp;
        }
    }


    private function updateRecord(array &$formPacket): void {
        $this->db->clearParams();

        $update = '';
        foreach ($this->rows as $row) {
            if ($row['Field'] !== 'createId' && $row['Field'] !== 'createDate') {
                $fld = $this->getField($row, $formPacket);
                // Auto-Increment-Felder und nicht übermittelte Felder nie aktualisieren
                if (!$fld->isAutoIncrement && $fld->isSet) {
                    if ($update) {
                        $update.= ', ';
                    }
                    $update .= '`' . $fld->name . '` = :'.$fld->name;
                    $this->db->prepareParam(':'.$fld->name, $fld->value, $fld->type);
                }
            }
        }

        $where = '';
        foreach ($this->primaryKeys as $primaryKey) {

            $primaryKeyValue = $primaryKey->value;

            // Weil die Value sich verändert haben kann
            if ($primaryKey->oldValue !== null) {
                $primaryKeyValue = $primaryKey->oldValue;
            }

            if ($where) {
                $where .= ' AND ';
            }
            $where .= '`' . $primaryKey->name . '` = :pk_'.$primaryKey->name;
            $this->db->prepareParam(':pk_'.$primaryKey->name, $primaryKeyValue, $primaryKey->type);

            if (!$primaryKey->value) {
                $primaryKey->value = $primaryKey->oldValue;
            }
        }

        $st = $this->db->prepare('
            UPDATE `' . $this->tableName . '`
            SET
            ' . $update . '
            WHERE
            ' . $where . '
        ');

        $this->db->bindParams($st);
        $st->execute();
    }
}
