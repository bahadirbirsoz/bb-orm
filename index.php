<?php

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_NAME", "store");
define("DB_PASS", "");


spl_autoload_register(function ($className) {
    if (substr($className, 0, 4) === "Ayep") {
        $path = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . ".php";

        if (file_exists($path)) {
            include_once $path;
        }
    }
});


$categoryList = \Ayep\Model\Category::find();

foreach ($categoryList as $category) {
    /* @var $category \Ayep\Model\Category */

    echo '<li>' . $category->category . '</li>';

}



//$prod = new \Ayep\Model\Product();








