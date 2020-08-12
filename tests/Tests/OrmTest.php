<?php

namespace BbOrm\Test\Tests;

use BbOrm\DataRow;
use BbOrm\Exceptions\UnknownPropertyException;
use BbOrm\Model;
use BbOrm\Test\Factory\EntityFactory;
use BbOrm\Test\Factory\FabricationFactory;
use BbOrm\Test\Factory\TestCaseSceneryFactory;
use BbOrm\Test\Models\Category;
use BbOrm\Test\Models\Post;
use BbOrm\Test\Models\Tag;
use BbOrm\Test\Models\TagEventLog;

class OrmTest extends OrmTestCase
{

    public function testKeyToDataFetching()
    {
        TestCaseSceneryFactory::cleanDatabase();
        TestCaseSceneryFactory::createCategories(5);

        $arr = Category::findMapOfName();

        $this->assertIsArray($arr);
        $this->assertCount(5, $arr);
    }

    public function testCustomSelectMethod()
    {
        TestCaseSceneryFactory::resetDatabase();
        $rows = DataRow::select("category left join post on post.category_id = category.id ");
        $this->assertIsArray($rows);
        $this->assertGreaterThan(10, $rows);
    }


    public function testCanDelete()
    {
        $category = EntityFactory::category();
        $this->assertNotNull($category->id);
        $category->remove();
    }

    public function testCanFind()
    {
        $categoryArray = Category::find();
        $this->assertIsArray($categoryArray);
    }

    public function testCanFindById()
    {
        $category = EntityFactory::category();
        $this->assertNotNull($category->id);

        $found = Category::find($category->id);
        $this->assertIsObject($found);

        $this->assertJsonStringEqualsJsonString(
            json_encode($category),
            json_encode($found)
        );
    }

//    public function testExecuteCustomSelect(){
//        $dataArr = Model::select("SELECT * FROM category inner join post on post.category_id = category.id");
//    }


    public function testPhalconStyleFind()
    {
        $category1 = EntityFactory::category();
        $category2 = EntityFactory::category();

        $found = Category::find(["conditions" => ' (id = ? OR id = ? ) ', "bind" => [$category1->id, $category2->id]]);

        $this->assertCount(2, $found);
    }

    public function testInsertWithExistingIdInTheEntity()
    {
        TestCaseSceneryFactory::cleanDatabase();
        $id = rand(1, 100);
        $post = FabricationFactory::post();
        $post->id = $id;
        $post->save();
        $foundPost = Post::find($id);
        $this->assertIsObject($foundPost);
        $this->assertEquals($post->id, $id);
    }


    public function testCanCreateEntity()
    {
        $category = EntityFactory::category();
        $this->assertIsNumeric($category->id);
    }

    public function testRemoveEntity()
    {
        $category = EntityFactory::category();
        $this->assertEquals(1, Category::count(['id' => $category->id]));
        $category->remove();
        $this->assertEquals(0, Category::count(['id' => $category->id]));

    }


}