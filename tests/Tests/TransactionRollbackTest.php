<?php


namespace BbOrm\Test\Tests;


use BbOrm\Connection;
use BbOrm\Exceptions\InsertFailedException;
use BbOrm\Exceptions\UpdateFailedException;
use BbOrm\Test\Factory\EntityFactory;
use BbOrm\Test\Factory\TestCaseSceneryFactory;
use BbOrm\Test\Models\Tag;
use PHPUnit\Framework\TestCase;

class TransactionRollbackTest extends TestCase
{

    public function testRollbackOnCreate()
    {
        TestCaseSceneryFactory::cleanDatabase();
        $pdoCon = Connection::getInstance()->getConnection();
        $pdoCon->beginTransaction();

        $tag = EntityFactory::tag();

        $this->expectException(InsertFailedException::class);
        Tag::createRow(['unknown_parameter' => 'some value']);
        $this->assertCount(0, Tag::find());

    }

    public function testRollbackOnUpdate()
    {
        TestCaseSceneryFactory::cleanDatabase();
        $tag = EntityFactory::tag();

        $pdoCon = Connection::getInstance()->getConnection();
        $pdoCon->beginTransaction();
        $tag->save();

        $this->expectException(UpdateFailedException::class);

        Tag::updateRow(['id' => $tag->id, 'unknown_parameter' => 'some value']);

        $this->assertCount(1, Tag::find());

    }

}