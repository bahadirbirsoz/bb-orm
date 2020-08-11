<?php

namespace BbOrm\Test\Tests;

use BbOrm\Test\Models\Category;
use PHPUnit\Framework\TestCase ;

class OrmTest extends TestCase
{

    public function testCanDeleteCategory(){
        $category = new \BbOrm\Test\Models\Category();
        $category->save();
        $this->assertIsNumeric($category->id);
    }

    public function testCanFindCategory(){
        $categoryArray = Category::find();
        $this->assertIsArray($categoryArray);
    }

    public function testCanFilterCategory(){

    }

    public function testCanFindById(){

    }


    public function testCanCreateCategory(){
        $category = new \BbOrm\Test\Models\Category();
        $category->save();
        $this->assertIsNumeric($category->id);
    }

}