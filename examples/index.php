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
$connectionString = new ORM\Adapter\Mysql([
    'server' => 'localhost',
    'port' => '3306',
    'database' => 'bazalt_cms',
    'username' => 'root',
    'password' => 'awdawd'
]);
$connection2 = ORM\Connection\Manager::add($connectionString, 'differentConnection');


// #1 Simple query
// select all
$q = new ORM\Query('SELECT * FROM `category`');
$categories = $q->fetchAll();
//print_r($categories);

// insert
$q = new ORM\Query('INSERT INTO `category`(`name`) VALUES (:name)', [
    'name' => 'New category'
]);
$q->exec();
//$q->connection($connection2)->exec();

$category_id = $q->getLastInsertId();

// update
$q = new ORM\Query('UPDATE `category` SET `name` = :name WHERE `category_id` = :category_id', [
    'category_id' => $category_id,
    'name' => 'My cool category'
]);
$q->exec();

// select one row
$q = new ORM\Query('SELECT * FROM `category` WHERE `category_id` = ?', $category_id);
$category = $q->fetch();
echo $category->category_id . ' - ' . $category->name;

// delete
$q = new ORM\Query('DELETE FROM `category` WHERE `category_id` = ?', $category_id);
$q->exec();
