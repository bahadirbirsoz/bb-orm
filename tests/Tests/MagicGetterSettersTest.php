<?php


namespace BbOrm\Test\Tests;


use BbOrm\Exceptions\UnknownPropertyException;
use BbOrm\Test\Factory\EntityFactory;
use BbOrm\Test\Factory\FabricationFactory;
use BbOrm\Test\Factory\TestCaseSceneryFactory;
use BbOrm\Test\Models\Category;
use BbOrm\Test\Models\Post;
use PHPUnit\Framework\TestCase;

class MagicMethodsTest extends TestCase
{
    public function testVirtualPropertyGetter(){
        $post = FabricationFactory::post();
        $this->assertEquals("computed or formatted value",$post->virtualProperty);
    }

    public function testFindByMagicMethod()
    {
        TestCaseSceneryFactory::cleanDatabase();
        TestCaseSceneryFactory::createCategoryWithPosts(3, 3);
        $category = Category::findOne();
        $posts = Post::findByCategoryId($category->id);
        $this->assertCount(3, $posts);
    }

    public function testFindOneByMagicMethod()
    {
        TestCaseSceneryFactory::resetDatabase();
        TestCaseSceneryFactory::createCategoryWithPosts(3, 3);

        $actual = Category::findOne();
        $expected = Category::findOneByName($actual->name);
        $this->assertJsonStringEqualsJsonString(
            json_encode($actual),
            json_encode($expected)
        );
    }



}