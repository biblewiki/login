<?php

class db
{
    protected $_db;
    protected $connected = false;
    protected $url;
    protected $user;
    protected $pw;
    protected $dbName;

    function __construct($url, $user, $pw, $dbName)
    {
        $this->url = $url;
        $this->user = $user;
        $this->pw = $pw;
        $this->dbName = $dbName;

        $this->_db = new mysqli($url, $user, $pw, $dbName);

        if (mysqli_connect_errno()) {
            throw new Exception("DB connection error: " . mysqli_connect_error());
        }

        $this->connected = true;
    }

    function getDB()
    {
        if ($this->connected)
            return $this->_db;

        throw new Exception("DB not connected");
        return;
    }

    function query($query)
    {
        if ($this->connected) {
            return $this->_db->query($query);
        }

        throw new Exception("DB not connected");
        return;
    }

    function prepare($query)
    {
        if ($this->connected) {
            $stmt = $this->_db->prepare($query);
            //print_r($this->_db);
            if ($this->_db->errno) {
                throw new Exception("DB error: " . $this->_db->error);
                return "no";
            }
            return $stmt;
        }

        throw new Exception("DB not connected");
        return "no";
    }

    public static function getTableAsArray($stmt)
    {
        $res = $stmt->get_result();

        $payloadArray = array();

        if ($res->num_rows > 0) {
            while ($array = $res->fetch_assoc()) {
                $payloadArray[] = $array;
            }
            return $payloadArray;
        } else {
            return array('error' => 'no entry found');
        }
    }

    public static function validateString($str)
    {
        //@todo some validation
        return $str;
    }
}
