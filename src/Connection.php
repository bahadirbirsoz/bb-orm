<?php

namespace BbOrm;

class Connection
{

    /**
     * @var Connection
     */
    static $instance;

    /**
     * @var \PDO
     */
    private $conn;

    private $locked = false;

    /**
     * @return Connection
     */

    protected function __construct($host, $db, $user, $pass)
    {
        if (!$this->locked) {
            $this->conn = new \PDO('mysql:host=' . $host . ';dbname=' . $db . ";charset=utf8", $user, $pass);
        }
    }

    /**
     * @return PDO
     */
    public function getConnection()
    {
        if (!$this->locked) {
            return $this->conn;
        }
    }

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new Connection(
                $_ENV['BBORM_HOSTNAME'],
                $_ENV['BBORM_DATABASE'],
                $_ENV['BBORM_USERNAME'],
                $_ENV['BBORM_PASSWORD']);
        }
        return static::$instance;
    }

    public function lock()
    {
        $this->locked = true;
    }

}

