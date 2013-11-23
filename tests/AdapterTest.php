<?php

namespace tests;

class AdapterTest extends ORMBaseCase
{   
    public function testMysqlConnectionString()
    {
        $string = new \Bazalt\ORM\Adapter\Mysql(
            array('server' => 'localhost', 'database' => 'bazalt_tests', 'username' => 'root', 'password' => 'test')
        );
        $this->assertEquals($string->toPDOConnectionString(),'mysql:host=localhost;port=3306;dbname=bazalt_tests');

        $this->assertEquals($string->getPassword(),'test');
        $this->assertEquals($string->getDatabase(),'bazalt_tests');
        $this->assertEquals($string->getUser(),'root');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMysqlConnectionStringInvalidOptions()
    {
        $string = new \Bazalt\ORM\Adapter\Mysql('test');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMysqlConnectionStringUnknownDatabase()
    {
        $string = new \Bazalt\ORM\Adapter\Mysql(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMysqlConnectionStringInvalidPort()
    {
        $string = new \Bazalt\ORM\Adapter\Mysql(array('database' => 'bazalt_tests', 'port' => 'wrong'));
    }
}