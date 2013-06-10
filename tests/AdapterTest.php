<?php

require_once 'bootstrap.inc';

class ORMTest_Adapter extends Tests\BaseCase
{   
    public function testMysqlConnectionString()
    {
        $string = new ORM_Adapter_Mysql(array('server' => 'localhost', 'database' => 'bazalt_tests', 'username' => 'root', 'password' => 'test'));
        $this->assertEquals($string->toPDOConnectionString(),'mysql:host=localhost;port=3306;dbname=bazalt_tests');

        $this->assertEquals($string->getPassword(),'test');
        $this->assertEquals($string->getDatabase(),'bazalt_tests');
        $this->assertEquals($string->getUser(),'root');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMysqlConnectionStringInvalidOptions()
    {
        $string = new ORM_Adapter_Mysql('test');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMysqlConnectionStringUnknownDatabase()
    {
        $string = new ORM_Adapter_Mysql(array());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMysqlConnectionStringInvalidPort()
    {
        $string = new ORM_Adapter_Mysql(array('database' => 'bazalt_tests', 'port' => 'wrong'));
    }
}