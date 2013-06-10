<?php

define('SITE_DIR', __DIR__);
require_once '../vendor/autoload.php';

use Bazalt\ORM as ORM;

$connectionString = new ORM\Adapter\Mysql([
    'server' => 'localhost',
    'port' => '3306',
    'database' => 'afisha',
    'username' => 'root',
    'password' => 'awdawd'
]);
$connection1 = ORM\Connection\Manager::add($connectionString, 'default');

// #3 Models
// select all
$q = Model\Category::select();
$categories = $q->fetchAll();
//print_r($categories);

// insert
$category = new Model\Category();
$category->name = 'New category';
$category->save();

// update
$category->name = 'My cool category';
$category->save();

// select one row
$category = Model\Category::getById($category->category_id);
echo $category->category_id . ' - ' . $category->name;

// delete
$category->delete();
