<?php

require_once __DIR__ . '/../vendor/autoload.php';

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

$connectionString = new Bazalt\ORM\Adapter\Mysql($dbParams);
Bazalt\ORM\Connection\Manager::add($connectionString, 'test');

// Autoloading is not available if using PHP in CLI interactive mode.
new ORMTest_Model_Actor();
new ORMTest_Model_Address();
new ORMTest_Model_Category();
new ORMTest_Model_City();
new ORMTest_Model_Country();
new ORMTest_Model_Customer();
new ORMTest_Model_Film();
new ORMTest_Model_FilmActor();
new ORMTest_Model_FilmCategory();
new ORMTest_Model_FilmText();
new ORMTest_Model_Inventory();
new ORMTest_Model_Language();
new ORMTest_Model_Payment();
new ORMTest_Model_Rental();
new ORMTest_Model_Staff();
new ORMTest_Model_Store();