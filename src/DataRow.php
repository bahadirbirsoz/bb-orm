<?php


namespace BbOrm;

class DataRow extends Model
{

    public function __set($name, $val)
    {
        $methodName = "set" . ucfirst(SyntaxHelper::snakeToCamel($name));
        if (method_exists($this, $methodName)) {
            return $this->$methodName($val);
        } else {
            $this->$name = $val;
        }
    }

}