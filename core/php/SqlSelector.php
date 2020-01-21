<?php
declare(strict_types = 1);

class SqlSelector {
    protected $_tablename;
    protected $_queryBld;
    protected $_individualSelect = false;

    public function __construct($tablename) {
        $this->_queryBld = new QueryBuilderForSelect();
        $this->_queryBld->addFromElement('`' . $tablename . '`');
        $this->_tablename = $tablename;
    }

    public function addSelectElement(string $sql): void {
        $this->_queryBld->addSelectElement($sql);
        $this->_individualSelect = true;
    }

    public function addFromElement(string $sql): void {
        $this->_queryBld->addFromElement($sql);
    }

    public function addWhereElement(string $sql): void {
        $this->_queryBld->addWhereElement($sql);
    }

    public function addOrderByElement(string $sql): void {
        $this->_queryBld->addOrderByElement($sql);
    }

    public function addParam($parameter, $variable, $data_type = \PDO::PARAM_STR, $length = 0, $driver_options = null): void {
        $this->_queryBld->addParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    public function setLimit(int $start, int $limit): void {
        $this->_queryBld->setLimit($start, $limit);
    }

    /**
     * @return QueryBuilderForSelect
     */
    public function getQueryBuilderForSelect(){
        return $this->_queryBld;
    }

    /**
     * Führt die Abfrage aus und gibt die Werte zurück.
     * @param Db $db
     * @param bool $fetchAll
     * @return array
     */
    public function execute(Db $db, bool $fetchAll = true) {

        if (!$this->_individualSelect) {
            $this->_queryBld->addSelectElement('`' . $this->_tablename . '`.*');
        }

        if (!$fetchAll) {
            $this->_queryBld->setLimit(0, 1);
        }

        // SQL ausführen
        $st = $db->prepare($this->_queryBld->getSql());
        $this->_queryBld->bindParams($st);
        $st->execute();
        if ($fetchAll) {
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            $row = \is_array($row) ? $row : [];
            $rows = [$row];
            unset ($row);
        }
        unset ($st);

        $fieldTypes = $this->getFieldTypes($db);

        foreach ($rows as &$row) {
            foreach ($row as $key => &$value) {
                $type = \array_key_exists($key, $fieldTypes) ? $fieldTypes[$key] : '';

                if ($value !== null) {
                    switch ($type) {
                        case 'TINYINT':
                        case 'SMALLINT':
                        case 'MEDIUMINT':
                        case 'INT':
                        case 'BIGINT':
                        case 'YEAR':
                        case 'TIMESTAMP':
                            $value = (int)$value;
                            break;

                        case 'DECIMAL':
                        case 'DEC':
                        case 'FLOAT':
                        case 'DOUBLE':
                            $value = (float)$value;
                            break;

                        case 'CHAR':
                        case 'VARCHAR':
                        case 'TINYTEXT':
                        case 'TEXT':
                        case 'MEDIUMTEXT':
                        case 'LONGTEXT':
                        case 'TIME':
                            $value = (string)$value;
                            break;

                        case 'DATETIME':
                        case 'DATE':
                            $value = strtotime($value);
                            break;

                        case 'BIT':
                            $value = (bool)$value;
                            break;

                        default:
                            if (is_numeric($value)) {
                                $value = (int)$value;

                            // Datum: 2018-01-01 10:22:111
                            } else if (preg_match('/^\d{4}\-\d{2}\-\d{2}(?: \d{2}\:\d{2}(?:\:\d{2}){0,1}){0,1}$/u', $value)) {
                                $value = strtotime($value);
                            }
                    }
                }
            }
            unset ($value);
        }
        unset ($row);

        return $fetchAll ? $rows : $rows[0];
    }



    protected function getFieldTypes(Db $db): array {
        // Spalten abfragen
        $st = $db->query('SHOW FIELDS FROM `' . $this->_tablename . '`');
        $flds = $st->fetchAll(\PDO::FETCH_ASSOC);
        unset($st);

        $fieldTypes = [];
        foreach ($flds as $fld) {
            $type = $fld['Type'];
            $type = str_replace('unsigned', '', $type);
            $type = preg_replace('/\([0-9,]+\)/u', '', $type);
            $fieldTypes[$fld['Field']] = mb_strtoupper(trim($type));
        }
        return $fieldTypes;
    }
}
