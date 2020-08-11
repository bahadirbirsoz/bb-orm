<?php

namespace BbOrm;

class Model
{

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
        return null;
    }

    public function __set($name, $val)
    {
        $methodName = "set" . ucfirst(SyntaxHelper::snakeToCamel($name));
        if (method_exists($this, $methodName)) {
            $this->$methodName($val);
        } else {
            $this->$name = $val;
        }
        return $this;
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
        }
        if (substr($name, 0, 9) == "findOneBy") {
            $key = SyntaxHelper::camelToSnake(substr($name, 9));
            return static::findOne([
                $key => $arguments[0]
            ]);
        }
    }


    public static function getPK()
    {
        return 'id';
        //return static::getTable() . "_id";
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
        return " ORDER BY " . implode(",", $arr);

    }

    public static function group($arr)
    {
        if (count($arr) == 0) {
            return "";
        }
        return " GROUP BY " . implode(",", $arr);

    }

    public static function limit($arr)
    {
        if (count($arr) == 0) {
            return "";
        }
        return " LIMIT " . implode(",", $arr);
    }

    public static function count($data = [], $order = [], $limit = [])
    {
        if (is_numeric($data)) {
            return static::findById($data);
        }
        $sql = "SELECT count(*) as c FROM `" . static::getTable() . "` " . static::where($data) . static::order($order) . static::limit($limit);

        $sth = static::getConnection()->prepare($sql);
        $sth->execute(static::conditions($data));
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
        return (int)$result[0]['c'];
    }

    /**
     * @param array $data
     * @return static[]
     */
    public static function find($data = [], $order = [], $limit = [])
    {
        if (is_numeric($data)) {
            return static::findById($data);
        }
        $sql = "SELECT * FROM `" . static::getTable() . "` " . static::where($data) . static::order($order) . static::limit($limit);

        $sth = static::getConnection()->prepare($sql);
        $sth->execute(static::conditions($data));
        return $sth->fetchAll(\PDO::FETCH_CLASS, get_called_class());
    }

    public static function raw($query)
    {
        $sth = static::getConnection()->prepare($query);
        $sth->execute();
        return $sth->fetchAll();
    }

    public static function select($table, $data = [], $order = [], $group = [], $limit = [], $fetchMethod = \PDO::FETCH_CLASS)
    {
        if (is_array($table)) {
            $data = $table;
            $table = static::getTable();
        }

        if (stristr(trim($table), "select ")) {
            $sql = $table . " " . static::where($data) . static::group($group) . static::order($order) . static::limit($limit);
        } else {
            $sql = "SELECT * FROM `" . $table . "` " . static::where($data) . static::group($group) . static::order($order) . static::limit($limit);
        }
        $sth = static::getConnection()->prepare($sql);

        $sth->execute(static::conditions($data));
        return $sth->fetchAll($fetchMethod, get_called_class());
    }

    public static function selectMap($table, $data = [], $order = [], $group = [])
    {
        $data = static::select($table, $data, $order, $group, \PDO::FETCH_ASSOC);
        if (count($data) == 0) {
            return [];
        }
        $keys = array_keys($data[0]);
        $result = [];
        foreach ($data as $row) {
            $result[$row[$keys[0]]] = $row[$keys[1]];
        }
        return $result;
    }

    public static function conditions($data = [])
    {
        if (!count($data)) {
            return [];
        }

        if (array_key_exists("bind", $data)) {
            return $data['bind'];
        }
        $bind = [];
        foreach ($data as $key => $val) {

            $bind[] = $val;
        }
        return $bind;
    }


    public static function findOne($data = [])
    {
        $arr = static::find($data);
        if (!$arr) {
            return false;
        }
        return $arr[0];
    }

    public static function findById($id)
    {
        return self::findOne([static::getPK() => $id]);
    }


    private function insertQueryString($data)
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

    private function updateQueryString($data)
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

    public function save()
    {
        if (!isset($this->{static::getPK()})) {
            return $this->insert();
        }

        $currentRec = static::findById($this->{static::getPK()});
        if ($currentRec) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }


    private function update()
    {

        if (method_exists($this, 'beforeSave')) {
            $this->beforeSave();
        }
        if (method_exists($this, 'beforeUpdate')) {
            $this->beforeUpdate();
        }

        $data = get_object_vars($this);
        $sql = self::updateQueryString($data);

        $sth = static::getConnection()->prepare($sql);

        if ($sth->execute($data) === false) {
            return false;
        }

        if (method_exists($this, 'afterUpdate')) {
            $this->afterUpdate();
        }
        if (method_exists($this, 'afterSave')) {
            $this->afterSave();
        }

        return true;
    }

    public function insert()
    {
        if (method_exists($this, 'beforeSave')) {
            $this->beforeSave();
        }
        if (method_exists($this, 'beforeInsert')) {
            $this->beforeInsert();
        }

        $data = get_object_vars($this);
        $sql = self::insertQueryString($data);
        $sth = static::getConnection()->prepare($sql);
        if ($sth->execute($data) === false) {
            return false;
        }
        $this->{static::getPK()} = static::getConnection()->lastInsertId();


        if (method_exists($this, 'afterInsert')) {
            $this->afterInsert();
        }
        if (method_exists($this, 'afterSave')) {
            $this->afterSave();
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

        $sth = static::getConnection()->prepare($sql);
        $sth->execute(static::conditions($data));
        $count = $sth->rowCount();
        return $count > 0;
    }

    /**
     * @return \PDO
     */
    public static function getConnection()
    {
        return Connection::getInstance()->getConnection();
    }


}


