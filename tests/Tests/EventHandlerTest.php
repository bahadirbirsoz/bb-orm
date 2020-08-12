<?php


namespace BbOrm\Test\Tests;


use BbOrm\Test\Factory\EntityFactory;
use BbOrm\Test\Factory\FabricationFactory;
use BbOrm\Test\Factory\TestCaseSceneryFactory;
use BbOrm\Test\Models\TagEventLog;
use PHPUnit\Framework\TestCase;

class EventHandlerTest extends TestCase
{

    public function testAfterSaveAndAfterUpdateEvent()
    {
        TestCaseSceneryFactory::resetDatabase();
        $tag = EntityFactory::tag();

        $this->assertEquals(1, TagEventLog::count([
            "tag_id" => $tag->id,
            'event' => 'afterSave'
        ]));

        $tag->save();

        $this->assertEquals(2, TagEventLog::count([
            "tag_id" => $tag->id,
            'event' => 'afterSave'
        ]));
        $this->assertEquals(1, TagEventLog::count([
            "tag_id" => $tag->id,
            'event' => 'afterUpdate'
        ]));

    }

    public function testAfterSaveAndAfterCreateEvent()
    {
        TestCaseSceneryFactory::resetDatabase();
        $tag = EntityFactory::tag();

        $this->assertEquals(1, TagEventLog::count([
            "tag_id" => $tag->id,
            'event' => 'afterSave'
        ]));
        $this->assertEquals(1, TagEventLog::count([
            "tag_id" => $tag->id,
            'event' => 'afterCreate'
        ]));
    }

    public function testBeforeSaveEvent()
    {
        TestCaseSceneryFactory::resetDatabase();
        $tag = FabricationFactory::tag();
        $this->assertNull($tag->saved_at);
        $tag->save();
        $this->assertIsString($tag->saved_at);
        $overwriteString = "shall be overwritten";
        $tag->saved_at = $overwriteString;

        $this->assertSame($tag->saved_at,$overwriteString);
        $tag->save();
        $this->assertNotSame($tag->saved_at,$overwriteString);
    }

    public function testBeforeCreateEvent()
    {
        TestCaseSceneryFactory::resetDatabase();
        $post = FabricationFactory::post();
        $post->save();
        $this->assertIsString($post->created_at);
        $this->assertNull($post->updated_at);
        $post->save();
        $this->assertIsString($post->updated_at);
    }

    public function testBeforeCreateAndBeforeUpdateEventHooks()
    {
        TestCaseSceneryFactory::resetDatabase();
        $post = EntityFactory::post();
        $this->assertNull($post->updated_at);
        $post->save();
        $this->assertIsString($post->updated_at);
    }


}