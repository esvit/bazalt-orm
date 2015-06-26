<?php

define('SITE_DIR', __DIR__);
require_once '../vendor/autoload.php';
require_once 'Model/Base/Category.php';
require_once 'Model/Base/NewsArticle.php';
require_once 'Model/Category.php';
require_once 'Model/NewsArticle.php';

use Bazalt\ORM as ORM;

$connectionString = new ORM\Adapter\Mysql([
    'server' => 'localhost',
    'port' => '3306',
    'database' => 'orm_examples',
    'username' => 'root',
    'password' => ''
]);
$connection1 = ORM\Connection\Manager::add($connectionString, 'default');

$q = new ORM\Query('SET FOREIGN_KEY_CHECKS=0');
$q->exec();

$q = new ORM\Query('TRUNCATE categories');
echo $q->toSql()."\n";
$q->exec();
echo "----------------\n";

// #2 Builders

// insert
$q = ORM::insert('Model\Category')
            ->set('name', 'New category');
$q->exec();
echo $q->toSql()."\n";
$category_id = $q->getLastInsertId();
print "category_id:" . $category_id."\n";
echo "----------------\n";

// update
$q = ORM::update('Model\Category')
            ->set('name', 'My cool category')
            ->where('category_id = ?', $category_id);
echo $q->toSql()."\n";
$q->exec();
echo "----------------\n";

$category = new Model\Category();
$category->name = 'New category 2';
$category->save();

$category = new Model\Category();
$category->name = 'New category 3';
$category->save();

// select one row
$q = ORM::select('Model\Category')
            ->where('category_id = ?', $category_id);
$category = $q->fetch();
echo $q->toSql()."\n";
echo $category->category_id . ' - ' . $category->name."\n";
echo "----------------\n";

// select many rows
$q = ORM::select('Model\Category')
            ->where('category_id > ?', 0)
            ->andWhere('name LIKE ?', '%cool%');
$categories = $q->fetchAll();
echo $q->toSql()."\n";
print_r($categories);
echo "----------------\n";

// select many rows
$q = ORM::select('Model\Category')
            ->where('category_id > ?', 0)
            //orWhere
            //andWhereIn
            //orWhereIn
            //andNotWhereIn
            //orNotWhereIn
            ->andWhere('name LIKE ?', '%cool%');
$categories = $q->fetchAll();
echo $q->toSql()."\n";
print_r($categories);
echo "----------------\n";

// select all
$q = ORM::select('Model\Category');
echo $q->toSql()."\n";
$categories = $q->fetchAll();
print_r($categories);
echo "----------------\n";

// delete
$q = ORM::delete('Model\Category')
            ->where('category_id = ?', $category_id);

echo $q->toSql()."\n";
$q->exec();
echo "----------------\n";