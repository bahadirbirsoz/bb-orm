<?php


namespace BbOrm\Test\Tests;


use BbOrm\Connection;
use BbOrm\Exceptions\AccessViolationException;
use BbOrm\Exceptions\DatabaseLockedException;
use BbOrm\Exceptions\InsertFailedException;
use BbOrm\Exceptions\UnknownPropertyException;
use BbOrm\Exceptions\UpdateFailedException;
use BbOrm\Test\Factory\EntityFactory;
use BbOrm\Test\Factory\FabricationFactory;
use BbOrm\Test\Factory\TestCaseSceneryFactory;
use BbOrm\Test\Models\Category;
use PHPUnit\Framework\TestCase;

class ExceptionHandlingTest extends TestCase
{
    public function testBadStaticMethodCallException()
    {
        $this->expectException(\BadMethodCallException::class);
        Category::someBadStaticMethodName();
    }


    public function testAccessPropertyNameThatDoesNotExistViaMagicGetter()
    {
        $this->expectException(UnknownPropertyException::class);
        $category = FabricationFactory::category();
        $category->some_property_name_that_does_not_exist;
    }

    public function testAccessPropertyNameThatDoesNotExistViaMagicSetter()
    {
        $this->expectException(UnknownPropertyException::class);
        $category = FabricationFactory::category();
        $category->some_property_name_that_does_not_exist = "";
    }


    public function testDatabaseLockedException()
    {
        TestCaseSceneryFactory::cleanDatabase();
        Connection::getInstance()->lock();
        $this->expectException(DatabaseLockedException::class);
        Connection::getInstance()->getConnection();
        TestCaseSceneryFactory::cleanDatabase();
    }


    public function testCreateEntityWithNonExistingProperty()
    {
        TestCaseSceneryFactory::resetDatabase();
        $category = FabricationFactory::category();
        $this->expectException(UnknownPropertyException::class);
        $category->title = "title";
        $category->save(['title' => 'there is no spoon']);
        $this->expectException(InsertFailedException::class);
        $this->expectExceptionMessage("Unknown column 'title' in 'field list'");
    }


    public function testAccessPrivatePropertyViaMagicGetter()
    {
        $post = FabricationFactory::post();
        $this->expectException(AccessViolationException::class);
        $post->url;
    }


    public function testAccessPrivatePropertyViaMagicSetter()
    {
        $post = FabricationFactory::post();
        $this->expectException(AccessViolationException::class);
        $post->url = "adsf-qw";
    }

    public function testDeleteById()
    {
        TestCaseSceneryFactory::cleanDatabase();
        TestCaseSceneryFactory::createCategories(5);

        $category = Category::findOne();

        Category::delete($category->id);
        $notFound = Category::find($category->id);
        $this->assertFalse($notFound);
    }

    public function testUpdateQueryWithWrongColumns()
    {
        TestCaseSceneryFactory::cleanDatabase();
        $category = EntityFactory::category();
        $this->expectException(UpdateFailedException::class);

        Category::updateRow([
            'id' => $category->id,
            'some_prop' => 'does not exist'
        ]);
    }

    public function testInsertQueryWithWrongColumns()
    {
        $this->expectException(InsertFailedException::class);
        Category::createRow(['some_prop' => 'does not exist']);
    }


}