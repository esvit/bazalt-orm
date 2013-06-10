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

// #2 Builders
// select all
//$q = new ORM\Query('SELECT * FROM `category`');
$q = ORM::select('category');
$categories = $q->fetchAll();
//print_r($categories);

// insert
/*$q = new ORM\Query('INSERT INTO `category`(`category_id`,`name`) VALUES (:category_id,:name)', [
    'category_id' => $category_id,
    'name' => 'New category'
]);*/
$q = ORM::insert('category')
            ->set('name', 'New category');
$q->exec();

$category_id = $q->getLastInsertId();
//$q->connection($connection2)->exec();

// update
/*$q = new ORM\Query('UPDATE `category` SET `name` = :name WHERE `category_id` = :category_id', [
    'category_id' => $category_id,
    'name' => 'My cool category'
]);*/
$q = ORM::update('category')
            ->set('name', 'My cool category')
            ->where('category_id = ?', $category_id);
$q->exec();

// select one row
//$q = new ORM\Query('SELECT * FROM `category` WHERE `category_id` = ?', $category_id);
$q = ORM::select('category')
            ->where('category_id = ?', $category_id);
$category = $q->fetch();
echo $category->category_id . ' - ' . $category->name;

// delete
//$q = new ORM\Query('DELETE FROM `category` WHERE `category_id` = ?', $category_id);
$q = ORM::delete('category')
            ->where('category_id = ?', $category_id);
$q->exec();
