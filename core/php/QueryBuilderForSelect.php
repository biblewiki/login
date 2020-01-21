<?php
declare(strict_types = 1);

class QueryBuilderForSelect {
    protected $_sqlDistinct = false;
    protected $_sqlSelectElements = [];
    protected $_sqlFromElements = [];
    protected $_sqlWhereElements = [];
    protected $_sqlGroupByElements = [];
    protected $_sqlHavingElements = [];
    protected $_sqlOrderByElements = [];
    protected $_sqlLimit = null;
    protected $_params = [];
    protected $_valueParams = [];


    /**
     * @param $sql
     */
    public function addFromElement(string $sql): void {
        if (!\in_array($sql, $this->_sqlFromElements, true)) {
            $this->_sqlFromElements[] = $sql;
        }
    }


    /**
     * @param $sql
     */
    public function addGroupByElement(string $sql): void {
        if (!\in_array($sql, $this->_sqlGroupByElements, true)) {
            $this->_sqlGroupByElements[] = $sql;
        }
    }


    /**
     * @param $sql
     */
    public function addHavingElement(string $sql): void {
        if (!\in_array($sql, $this->_sqlHavingElements, true)) {
            $this->_sqlHavingElements[] = $sql;
        }
    }


    /**
     * @param $sql
     */
    public function addOrderByElement(string $sql): void {
        if (!\in_array($sql, $this->_sqlOrderByElements, true)) {
            $this->_sqlOrderByElements[] = $sql;
        }
    }


    /**
     * @param $sql
     */
    public function addSelectElement(string $sql): void {
        if (!\in_array($sql, $this->_sqlSelectElements, true)) {
            $this->_sqlSelectElements[] = $sql;
        }
    }


    /**
     * @param $sql
     */
    public function addWhereElement(string $sql): void {
        if (!\in_array($sql, $this->_sqlWhereElements, true)) {
            $this->_sqlWhereElements[] = '(' . $sql . ')';
        }
    }


    /**
     * @param string $parameter
     * @param $variable
     * @param int $data_type
     * @param int $length
     * @param mixed $driver_options
     */
    public function addParam(string $parameter, $variable, int $data_type = \PDO::PARAM_STR, int $length = 0, $driver_options = null): void {
        $par = [$parameter, $variable, $data_type, $length, $driver_options];
        if (!\in_array($par, $this->_params, true)) {
            $this->_params[] = $par;
        }
    }


    /**
     * @param $parameter
     * @param $value
     * @param int $data_type
     */
    public function addValueParam(string $parameter, $value, int $data_type = \PDO::PARAM_STR): void {
        $par = [$parameter, $value, $data_type];
        if (!\in_array($par, $this->_valueParams, true)) {
            $this->_valueParams[] = $par;
        }
    }


    /**
     * @param \PDOStatement $st
     */
    public function bindParams(\PDOStatement $st): void {
        foreach ($this->_params as $param) {
            $st->bindParam($param[0], $param[1], $param[2], $param[3], $param[4]);
        }
        foreach ($this->_valueParams as $param) {
            $st->bindValue($param[0], $param[1], $param[2]);
        }
    }


    /**
     * @param \PDOStatement $st
     * @param $sql
     */
    public function bindUsedParams(\PDOStatement $st, string $sql): void {
        foreach ($this->_params as $param) {
            if (mb_strpos($sql, $param[0]) !== false) {
                $st->bindParam($param[0], $param[1], $param[2], $param[3], $param[4]);
            }
        }
        foreach ($this->_valueParams as $param) {
            if (mb_strpos($sql, $param[0]) !== false) {
                $st->bindValue($param[0], $param[1], $param[2]);
            }
        }
    }


    /**
     * Löscht alle Angaben
     */
    public function clear(): void {
        $this->_sqlDistinct = false;
        $this->_sqlSelectElements = [];
        $this->_sqlFromElements = [];
        $this->_sqlWhereElements = [];
        $this->_sqlGroupByElements = [];
        $this->_sqlHavingElements = [];
        $this->_sqlOrderByElements = [];
        $this->_sqlLimit = null;
        $this->_params = [];
        $this->_valueParams = [];
    }


    /**
     * Löscht die Parameter
     */
    public function clearParams(): void {
        $this->_params = [];
    }


    /**
     * @return string
     */
    public function getSql(): string {
        $select = implode(', ', $this->_sqlSelectElements);
        $from = implode(' ', $this->_sqlFromElements);
        $where = implode(' AND ', $this->_sqlWhereElements);
        $groupBy = implode(', ', $this->_sqlGroupByElements);
        $having = implode(' AND ', $this->_sqlHavingElements);
        $orderBy = implode(', ', $this->_sqlOrderByElements);

        $sql = '';
        if ($select) {
            $sql .= 'SELECT ';
            if ($this->_sqlDistinct) {
                $sql .= 'DISTINCT ';
            }
            $sql .= $select . ' ';
        }
        if ($from) {
            $sql .= 'FROM ' . $from . ' ';
        }
        if ($where) {
            $sql .= 'WHERE ' . $where . ' ';
        }
        if ($groupBy) {
            $sql .= 'GROUP BY ' . $groupBy . ' ';
        }
        if ($having) {
            $sql .= 'HAVING ' . $having . ' ';
        }
        if ($orderBy) {
            $sql .= 'ORDER BY ' . $orderBy . ' ';
        }
        if ($this->_sqlLimit) {
            $sql .= $this->_sqlLimit;
        }

        return $sql;
    }


    /**
     * @param $value
     */
    public function setDistinct(bool $value): void {
        $this->_sqlDistinct = (bool)$value;
    }


    /**
     * @param int|null $start
     * @param int|null $limit
     * @return void
     */
    public function setLimit(?int $start, ?int $limit): void {
        if (($start === 0 || $start) && ($limit === 0 || $limit)) {
            $this->_sqlLimit = "LIMIT {$start}, {$limit}";
        } else {
            $this->_sqlLimit = "";
        }
    }

}

