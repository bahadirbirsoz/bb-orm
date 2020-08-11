<?php


namespace BbOrm\Test\Tests;


use BbOrm\Model;
use BbOrm\Test\Models\Category;
use PHPUnit\Framework\TestCase;

class OrmTestCase extends TestCase
{

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        Model::raw("DELETE * from category");
        Model::raw("DELETE * from post");
        Model::raw("DELETE * from post_tag");
        Model::raw("DELETE * from tag");
    }

    protected function createCategory(){
        $cat = new Category();
        $cat->name = "";
    }

}