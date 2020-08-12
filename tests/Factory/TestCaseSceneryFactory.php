<?php


namespace BbOrm\Test\Factory;


use BbOrm\Connection;
use BbOrm\Model;
use BbOrm\Test\Models\Category;
use BbOrm\Test\Models\Post;
use BbOrm\Test\Models\PostTag;
use BbOrm\Test\Models\Tag;

class TestCaseSceneryFactory
{
    public static function cleanDatabase(){
        Connection::$instance = null;
        Model::raw("DELETE from category");
        Model::raw("DELETE from post");
        Model::raw("DELETE from post_tag");
        Model::raw("DELETE from tag");
        Model::raw("DELETE from tag_event_log");
    }

    public static function createPosts($n=3, $categoryId = null){
        for ($i = 0; $i< $n ;$i++){
            EntityFactory::post($categoryId);
        }
    }

    public static function createCategories($n=3){
        for ($i = 0; $i< $n ;$i++){
            EntityFactory::category();
        }
    }

    public static function createCategoryWithPosts($numberOfCategory, $numberOfPostsInEachCategory){
        for ($i = 0; $i< $numberOfCategory ;$i++){
            $category = EntityFactory::category();
            for($j = 0; $j < $numberOfPostsInEachCategory;$j++){
                $post = FabricationFactory::post();
                $post->category_id = $category->id;
                $post->save();
            }
        }
    }

    public static function resetDatabase(){
        static::cleanDatabase();
        Connection::$instance = null;
        static::createCategoryPostsAndTags();

    }

    public static function createCategoryPostsAndTags(){
        static::createTags(rand(15,30));
        static::createCategories(rand(3,5));

        /** @var Category $categoryArr */
        $categories = Category::find();
        foreach ($categories as $category){
            static::createPosts(rand(4,6),$category->id);
        }

        $posts = Post::find();
        foreach ($posts as $post){
            $tags = Tag::find([],['rand()'],[3,6]);
            foreach ($tags as $tag){
                EntityFactory::postTag($post->id,$tag->id);
            }
        }


    }

    public static function createTags($n=3){
        for ($i = 0; $i< $n ;$i++){
            EntityFactory::tag();
        }
    }


}