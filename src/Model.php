<?php

namespace BbOrm;

use BbOrm\Exceptions\AccessViolationException;
use BbOrm\Exceptions\InsertFailedException;
use BbOrm\Exceptions\UnknownPropertyException;
use BbOrm\Exceptions\UpdateFailedException;

class Model
{

    public function __get($name)
    {
        $methodName = "get" . ucfirst(SyntaxHelper::snakeToCamel($name));
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }
        if (property_exists($this, $name)) {
            throw new AccessViolationException();
        }
        throw new UnknownPropertyException($name);
    }

    public function __set($name, $val)
    {
        $methodName = "set" . ucfirst(SyntaxHelper::snakeToCamel($name));
        if (method_exists($this, $methodName)) {
            return $this->$methodName($val);
        }
        if (property_exists($this, $name)) {
            throw new AccessViolationException();
        }
        throw new UnknownPropertyException($name);
    }


    public static function getTable()
    {
        $arr = explode("\\", get_called_class());
        return SyntaxHelper::camelToSnake(array_pop($arr));
    }


    public static function __callStatic($name, $arguments)
    {
        if (substr($name, 0, 6) == "findBy") {
            $key = SyntaxHelper::camelToSnake(substr($name, 6));
            return static::find([
                $key => $arguments[0]
            ]);
        } elseif (substr($name, 0, 9) == "findOneBy") {
            $key = SyntaxHelper::camelToSnake(substr($name, 9));
            return static::findOne([
                $key => $arguments[0]
            ]);
        } elseif (substr($name, 0, 9) == "findMapOf") {
            $key = SyntaxHelper::camelToSnake(substr($name, 9));
            return static::findMapOf($key);
        } else {
            throw new \BadMethodCallException();
        }
    }


    public static function findMapOf($propertyName, $where = [], $order = [], $group = [], $limit = [])
    {
        $query = "select `" . static::getPK() . "`,`" . $propertyName . "` from " . static::getTable() . " ";
        return static::select($query, $where, $order, $group, $limit, \PDO::FETCH_CLASS, get_called_class());

        $result = [];
        foreach ($rows as $row) {
            $result[$row[0]] = $row[1];
        }
        return $result;

    }

    public static function getPK()
    {
        return 'id';
    }

    public static function where($data)
    {

        if (count($data) == 0) {
            return "";
        }
        if (array_key_exists("conditions", $data)) {
            return " WHERE " . $data["conditions"] . " ";
        } else {
            return " WHERE `" . implode("`=? AND `", array_keys($data)) . "`=? ";
        }
    }

    public static function order($arr)
    {
        if (count($arr) == 0) {
            return "";
        }
        return " ORDER BY " . implode(",", $arr) . ' ';

    }

    public static function group($arr)
    {
        if (count($arr) == 0) {
            return "";
        }
        return " GROUP BY " . implode(",", $arr) . ' ';

    }

    public static function limit($arr)
    {
        if (count($arr) == 0) {
            return "";
        }
        return " LIMIT " . implode(",", $arr) . ' ';
    }

    public static function count($where = [], $order = [], $group = [], $limit = [])
    {
        $query = "select count(*) as c from `" . static::getTable() . "` ";
        $result = static::select($query, $where, $order, $group, $limit, \PDO::FETCH_NUM);
        return (int)$result[0][0];
    }

    /**
     * @param array $data
     * @return static[]
     */
    public static function find($where = [], $order = [], $group = [], $limit = [], $fetchMethod = \PDO::FETCH_CLASS)
    {
        if (is_numeric($where)) {
            return static::findOneById($where);
        }

        return static::select(static::getTable(), $where, $order, $group, $limit, \PDO::FETCH_CLASS, get_called_class());
    }

    public static function raw($query)
    {
        $sth = static::getPdoConnection()->prepare($query);
        $sth->execute([]);
        return $sth->fetchAll();
    }

    public static function select($tableNameOrSelectPartOfQuery, $where = [], $order = [], $group = [], $limit = [], $fetchMethod = \PDO::FETCH_CLASS, $rowClass = null)
    {
        if ($rowClass == null) {
            $rowClass = get_called_class();
        }
        if (stristr(trim($tableNameOrSelectPartOfQuery), "select")) {
            $sql = $tableNameOrSelectPartOfQuery . " " . static::where($where) . static::group($group) . static::order($order) . static::limit($limit);
        } else {
            $sql = "SELECT * FROM `" . $tableNameOrSelectPartOfQuery . "` " . static::where($where) . static::group($group) . static::order($order) . static::limit($limit);
        }

        $sth = static::getPdoConnection()->prepare($sql);

        $sth->execute(static::conditions($where));
        if ($fetchMethod == \PDO::FETCH_CLASS) {
            return $sth->fetchAll($fetchMethod, $rowClass);
        } else {
            return $sth->fetchAll($fetchMethod);
        }
    }

    public static function conditions($where = [])
    {
        if (!count($where)) {
            return [];
        }
        if (array_key_exists("bind", $where)) {
            return $where['bind'];
        }
        $bind = [];
        foreach ($where as $key => $val) {
            $bind[] = $val;
        }
        return $bind;
    }


    public static function findOne($data = [], $order = [])
    {
        $arr = static::find($data, $order);
        if (!$arr) {
            return false;
        }
        return $arr[0];
    }

    private static function insertQueryString($data)
    {
        $keys = array_keys($data);
        return "INSERT INTO `" . static::getTable() . "` " . static::getInsertCols($keys) . " VALUES " . static::getInsertValues($keys);
    }

    private static function getInsertCols($keys)
    {
        return "(`" . implode("`,`", $keys) . "`)";
    }

    private static function getInsertValues($keys)
    {
        return "( :" . implode(",:", $keys) . " )";
    }

    private static function updateQueryString($data)
    {
        $pk = static::getPK();
        unset($data[$pk]);
        $keys = array_keys($data);
        $update = "UPDATE `" . static::getTable() . "` SET ";

        foreach ($keys as $key) {
            $update .= "`$key`=:$key ,";
        }
        $update = rtrim($update, ",");
        $update .= " WHERE `$pk` = :$pk ";
        return $update;
    }

    public function save($data = null)
    {
        if (!isset($this->{static::getPK()})) {
            return $this->create($data);
        }

        $currentRec = static::findOneById($this->{static::getPK()});
        if ($currentRec) {
            return $this->update($data);
        } else {
            return $this->create($data);
        }
    }


    public static function updateRow($data)
    {
        $connection = static::getPdoConnection();
        $sql = self::updateQueryString($data);

        $sth = $connection->prepare($sql);

        if ($sth->execute($data) === false) {
            $err = $sth->errorInfo();
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw new UpdateFailedException($err[2]);
        }
    }

    private function update()
    {
        $connection = static::getPdoConnection();
        $alreadyInTransaction = $connection->inTransaction();
        if (!$alreadyInTransaction) {
            $connection->beginTransaction();
        }
        if (method_exists($this, 'beforeSave')) {
            $this->beforeSave();
        }
        if (method_exists($this, 'beforeUpdate')) {
            $this->beforeUpdate();
        }

        $data = get_object_vars($this);
        static::updateRow($data);

        if (method_exists($this, 'afterUpdate')) {
            $this->afterUpdate();
        }
        if (method_exists($this, 'afterSave')) {
            $this->afterSave();
        }
        if (!$alreadyInTransaction) {
            $connection->commit();
        }
        return true;
    }

    public static function createRow($data)
    {
        $connection = static::getPdoConnection();
        $sql = self::insertQueryString($data);
        $sth = $connection->prepare($sql);
        if ($sth->execute($data) === false) {
            $err = $sth->errorInfo();
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw new InsertFailedException($err[2]);
        }
    }

    public function create()
    {

        $connection = static::getPdoConnection();
        $alreadyInTransaction = $connection->inTransaction();

        if (!$alreadyInTransaction) {
            $connection->beginTransaction();
        }
        if (method_exists($this, 'beforeSave')) {
            $this->beforeSave();
        }
        if (method_exists($this, 'beforeCreate')) {
            $this->beforeCreate();
        }

        static::createRow(get_object_vars($this));

        $this->{static::getPK()} = $connection->lastInsertId();

        if (method_exists($this, 'afterCreate')) {
            $this->afterCreate();
        }
        if (method_exists($this, 'afterSave')) {
            $this->afterSave();
        }
        if (!$alreadyInTransaction) {
            $connection->commit();
        }
        return true;
    }


    public function remove()
    {
        return static::delete([
            static::getPK() => $this->{static::getPK()}
        ]);
    }

    public static function delete($data = [])
    {
        if (is_numeric($data)) {
            $data = [
                static::getPK() => $data
            ];
        }
        $sql = "DELETE FROM " . static::getTable() . " " . static::where($data);

        $sth = static::getPdoConnection()->prepare($sql);
        $sth->execute(static::conditions($data));
        $count = $sth->rowCount();
        return $count > 0;
    }

    /**
     * @return \PDO
     */
    public static function getPdoConnection()
    {
        return Connection::getInstance()->getConnection();
    }


}


