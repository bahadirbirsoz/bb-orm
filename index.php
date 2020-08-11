<?php

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_NAME", "store");
define("DB_PASS", "");

$categoryList = \Ayep\Model\Category::find();

foreach ($categoryList as $category) {
    /* @var $category \Ayep\Model\Category */

    echo '<li>' . $category->category . '</li>';

}



//$prod = new \BbOrm\Model\Product();








