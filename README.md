# bb-orm
A simple lightweight ORM Library for PHP Projects.

### PHPUnit Code Coverage
https://bb-orm.bahadirbirsoz.com/coverage 

# Getting Started

### Writing Your First Model 
```
class Category extends \BbOrm\Model
{
    public $id;
    public $name;
    public $sorting;
}
```
- Models are supposed to extended from `\BbOrm\Model` class. 
- `snake_case` representation of `PascalCaseModelName` is assumed to be table name. Can be overwritten via `::getTable` method.
- `$id` is assumed to be the primary key in each table. Can be overwritten via `::getPK` method.  
- Every table must have one and only one primary key.
- Composite primary keys are not supported

```
class Category extends \BbOrm\Model
{
    public $category_id;
    public $name;
    public $sorting;

    public static function getTable(){
        return "table_name_in_database"; 
        //by default it was category, overwritten to table_name_in_database 
    }

    public static function getPK(){
        return "category_id";
        //by default it was id, overwritten to category_id
    }
}
```

### Access Layeres
Getter and Setters methods will be called by magic `::__get` and `::__set` methods if exists. Otherview will throw `\BbOrm\Exceptions\AccessViolationException`  

````
class Post extends Model
{
    protected $id;
    protected $title;
    protected $url;

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        $url = preg_replace('/[^\w\-]+/u', '-', $title);
        $this->url = mb_strtolower(preg_replace('/--+/u', '-', $url), 'UTF-8');
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
````

Usage
````
    $postObj = new Post();
    $postObj->title = "Set title will be called by magic ::__set"

````

### Custom Event Handlers
There are custom events hooks that bind functions to custom events. Following interfaces can be implemented for event hooks. 
- `\BbOrm\EventHandlers\BeforeCreate` 
- `\BbOrm\EventHandlers\BeforeUpdate` 
- `\BbOrm\EventHandlers\BeforeSave` 
- `\BbOrm\EventHandlers\AfterCreate` 
- `\BbOrm\EventHandlers\AfterUpdate` 
- `\BbOrm\EventHandlers\AfterSave`  
````
use BbOrm\EventHandlers\BeforeCreate;
use BbOrm\EventHandlers\BeforeUpdate;
use BbOrm\Model;
class Post extends Model implements BeforeCreate, BeforeUpdate
{
    public $id;
    public $created_at;
    public $updated_at;
    
    public function beforeCreate()
    {
        $this->created_at = date("Y-m-d H:i:s");
    }

    public function beforeUpdate()
    {
        $this->updated_at = date("Y-m-d H:i:s");
    }
}    
````

Transaction begins with entity save method gets called. If an event hook fails, transaction rollbacks implicitly. 

````

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
````

### Finding All Rows
```
public static function find($where = [], $order = [], $group = [], $limit = [], $fetchMethod = \PDO::FETCH_CLASS);
```

```
$posts = Post::find();
```

```
$posts = Post::find([
    "category_id" => $category->id
]);
```
```
$posts = Post::find([
    "conditions" => "created_at > ? AND category_id = ?",
    "bind" => [
        date('Y-m-d H:i:s' , strtotime('-7 days') ),
        $category->id
    ]
]);

```

### Finding One Row
```
public static function findOne($data = [], $order = []);
```
```
$category = Category::findOne();

```

```
$category = Category::findOne([
    'category_id' => '17'
]);
```
```
$category = Category::findOneById(17);
```
when an integer argument is provided as first argument to find method, find method works as findOneById   
```
$category = Category::find(17);
```


```
$lastCategory = Category::findOne([],['id desc']);

```


```
$posts = Post::find([
    "category_id" => $category->id
]);

```


### Magic Find Methods
`::findOneByPropertyName` and `::findByPropertyName` magic methods exists and.  
```
$posts = Post::findByCategoryId($category->id);
```

```
$categoryId = 17; //Sanitized argument
$post = Post::findByCategoryId($categoryId);

if(!$post){
    //404, returns false if not found
}else{
    // ...
}
```
```
$url = 'parsed-url';
$post = Post::findOneByUrl($url);

if(!$post){
    //404, returns false if not found
}else{
    // ...
}
```

### Phalcon-Style Find Calls

```
$posts = Post::find([
    "conditions" => "category_id = ?",
    "bind" => [
        $category->id
    ]
]);

```

```
$posts = Post::find([
    "conditions" => "created_at > ? AND category_id = ?",
    "bind" => [
        date('Y-m-d H:i:s' , strtotime('-7 days') ),
        $category->id
    ]
]);
```


### Saving Entities

```
$post = new Post();
$post->title = 'title';
$post->save();
```
```
$category = Category::find();
$category->name = 'New cateogry name';
$category->save();
```

### Deleting Rows
You can remove an entity, or call static `::delete` method for custom delete queries. 
```
$category = Category::find();
$category->name = 'New cateogry name';
$category->remove();
```

```
Post::delete([
    'category_id' => $category->id
])
```

### Custom Queries
You can run custom queries.
```
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
```

You can also define query classes for representation.

### More Information
Fore more information, you can read the unit tests (for now).

# Tests

I have tested with php 7.2.2 and 7.4. You can run tests in your environment. 

# Dev
Development project is containerized via docker. You can checkout the dev branch and run tests in your environment. 

#Licence

@Copyright 2020 Bahadır Birsöz <bahadirbirsoz@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.



  