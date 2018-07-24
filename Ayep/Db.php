<?php

namespace Ayep;

class Db
{

    /**
     * @var Db
     */
    static $instance;

    /**
     * @var \PDO
     */
    private $conn;

    /**
     * @return Db
     */

    protected function __construct($host, $db, $user, $pass)
    {
        if (!defined("Rumix_DB_LOCKED")) {
            $this->conn = new \PDO('mysql:host=' . $host . ';dbname=' . $db . ";charset=utf8", $user, $pass);
        }
    }

    /**
     * @return PDO
     */
    public function getConnection()
    {
        if (!defined("Rumix_DB_LOCKED")) {
            return $this->conn;
        }
    }

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new Db(DB_HOST, DB_NAME, DB_USER, DB_PASS);
        }
        return static::$instance;
    }


}

