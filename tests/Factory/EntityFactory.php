<?php

namespace BbOrm\Test\Factory;

use BbOrm\Test\Models\Category;
use BbOrm\Test\Models\Post;
use BbOrm\Test\Models\PostTag;
use BbOrm\Test\Models\Tag;

class EntityFactory
{
    /**
     * @return Category
     */
    public static function category(): Category
    {
        $category = FabricationFactory::category();
        $category->save();
        return $category;
    }


    /**
     * @return Post
     */
    public static function post($categoryId = null): Post
    {
        $post = FabricationFactory::post();
        if (!null) {
            $post->category_id = $categoryId;
        }
        $post->save();
        return $post;
    }
    /**
     * @return PostTag
     */
    public static function postTag($postId, $tagId): PostTag
    {
        $postTag = new PostTag();
        $postTag->post_id = $postId;
        $postTag->tag_id = $tagId;
        $postTag->save();
        return $postTag;
    }

    /**
     * @return Tag
     */
    public static function tag(): Tag
    {
        $tag = FabricationFactory::tag();
        $tag->save();
        return $tag;
    }
}
