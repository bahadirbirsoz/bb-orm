<?php


namespace BbOrm\Test\Models;


use BbOrm\EventHandlers\BeforeCreate;
use BbOrm\EventHandlers\BeforeUpdate;
use BbOrm\Model;

/**
 * Class Post
 * @package BbOrm\Test\Models
 * @property $virtualProperty
 */
class Post extends Model implements BeforeCreate, BeforeUpdate
{

    public $id;
    protected $title;
    protected $url;
    public $content;
    public $category_id;
    public $created_at;
    public $updated_at;

    public function getVirtualProperty(){
        return "computed or formatted value";
    }

    public function beforeCreate()
    {
        $this->created_at = date("Y-m-d H:i:s");
    }

    public function beforeUpdate()
    {
        $this->updated_at = date("Y-m-d H:i:s");
    }


    public function setTitle($title){
        $this->title = $title;
        $url = preg_replace('/[^\w\-]+/u', '-', $title);
        $this->url = mb_strtolower(preg_replace('/--+/u', '-', $url), 'UTF-8');
    }

    public function getTitle(){
        return $this->title;
    }

}