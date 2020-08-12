<?php


namespace BbOrm\Test\Models;


use BbOrm\EventHandlers\AfterCreate;
use BbOrm\EventHandlers\AfterSave;
use BbOrm\EventHandlers\AfterUpdate;
use BbOrm\EventHandlers\BeforeCreate;
use BbOrm\EventHandlers\BeforeSave;
use BbOrm\EventHandlers\BeforeUpdate;
use BbOrm\Model;

class Tag extends Model implements AfterCreate, AfterSave, AfterUpdate, BeforeSave
{
    public $id;
    public $tag;
    public $saved_at;

    public function beforeSave()
    {
        $this->saved_at = date("Y-m-d H:i:s");
    }

    public function afterCreate()
    {
        $event = new TagEventLog();
        $event->tag_id = $this->id;
        $event->event = 'afterCreate';
        $event->save();
    }

    public function afterUpdate()
    {
        $event = new TagEventLog();
        $event->tag_id = $this->id;
        $event->event = 'afterUpdate';
        $event->save();
    }

    public function afterSave()
    {
        $event = new TagEventLog();
        $event->tag_id = $this->id;
        $event->event = 'afterSave';
        $event->save();
    }

}
