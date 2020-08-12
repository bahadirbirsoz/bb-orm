<?php


namespace BbOrm\Test\Factory;


use BbOrm\Test\Models\Category;
use BbOrm\Test\Models\Post;
use BbOrm\Test\Models\Tag;

class FabricationFactory
{

    /**
     * @return Tag
     */
    static function tag():Tag
    {
        $faker = \Faker\Factory::create();
        $tag = new Tag();
        $tag->tag = $faker->name;
        return $tag;
    }

    /**
     * @return Category
     */
    static function category():Category
    {
        $faker = \Faker\Factory::create();
        $cat = new Category();
        $cat->name = $faker->name;
        return $cat;
    }

    /**
     * @return Post
     */
    static function post():Post
    {
        $faker = \Faker\Factory::create();
        $post = new Post();
        $post->content = $faker->text;
        $post->title = $faker->text;
        return $post;
    }
}