<?php


namespace BbOrm\Test\Tests;


use BbOrm\DataRow;
use BbOrm\Model;
use BbOrm\Test\Factory\TestCaseSceneryFactory;
use BbOrm\Test\Models\Category;
use BbOrm\Test\Models\Tag;
use PHPUnit\Framework\TestCase;

class CustomQueryTest extends TestCase
{

    public function testLimit()
    {
        TestCaseSceneryFactory::cleanDatabase();
        $createCount = 10;
        $limitCount = 3;
        TestCaseSceneryFactory::createCategories($createCount);
        $items = Category::find([], [], [], [0, $limitCount]);
        $this->assertCount($limitCount, $items);

    }

    public function testCount()
    {
        $expectedCount = 10;
        TestCaseSceneryFactory::cleanDatabase();
        TestCaseSceneryFactory::createCategories($expectedCount);
        $actualCount = Category::count();
        $this->assertEquals($expectedCount, $actualCount);
    }


    public function testOrderBy()
    {
        TestCaseSceneryFactory::resetDatabase();
        $unordered = Tag::find();
        $ordered = Tag::find([], ['tag']);
        $this->assertCount(count($unordered), $ordered);

        usort($unordered,
            function ($a, $b) {
                return strcmp($a->tag, $b->tag);
            }
        );

        $this->assertJsonStringEqualsJsonString(
            json_encode($unordered),
            json_encode($ordered)
        );
    }

    public function testCustomSelectGroupBy()
    {
        TestCaseSceneryFactory::resetDatabase();
        $query = "select
    tag.*,
	count(distinct(post.id)) as postCount,
    count(distinct(category.id)) as categoryCount
from
    tag
left join post_tag
    on post_tag.tag_id = tag.id
left join post
    on  post.id = post_tag.post_id
left join category
    on category.id = post.category_id
";


        $items = DataRow::select($query, [], [], ['tag.id']);

        $this->assertIsArray($items);
        $this->assertGreaterThan(10, count($items));
        $randomMetricObject = $items[rand(0, count($items) - 1)];
        $this->assertIsNumeric($randomMetricObject->postCount);
    }

}