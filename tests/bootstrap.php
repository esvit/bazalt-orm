<?php

require_once (is_file(__DIR__ . '/../vendor/autoload.php') ? (__DIR__ . '/../vendor/autoload.php') : '../vendor/autoload.php');

/*
    CREATE USER 'test'@'localhost' IDENTIFIED BY 'test';
    GRANT ALL PRIVILEGES ON *.* TO 'test'@'localhost' WITH GRANT OPTION;
*/

// init cache
/*using('Framework.System.Cache');
Cache::Singleton()->initCache('Cache_Memcache_Adapter', array('host' => 'localhost', 'port' => 11211));
Cache::Singleton()->salt('tests'); // cache salt, for memcache*/

$dbParams = array(
    'server' => $GLOBALS['db_host'],
    'username' => $GLOBALS['db_username'],
    'password' => $GLOBALS['db_password'],
    'database' => $GLOBALS['db_name'],
    'port' => $GLOBALS['db_port']
);

$connectionString = new \Bazalt\ORM\Adapter\Mysql($dbParams);
\Bazalt\ORM\Connection\Manager::add($connectionString, 'test');