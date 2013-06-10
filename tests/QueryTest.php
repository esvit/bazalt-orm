<?php

require_once 'bootstrap.inc';

class ORMTest_Query extends Tests\BaseCase
{
    protected $testObj;

    protected function setUp()
    {
    }
    
    protected function tearDown()
    {
    }

    /**
     * @covers ORM_Query::toSQL
     */
    public function testGetTable()
    {
        $q = new ORM_Query('SELECT * FROM actors WHERE id = ?', array(1));
        
        $this->assertEquals('SELECT * FROM actors WHERE id = 1', $q->toSQL());

        $q = new ORM_Query('SELECT * FROM actors WHERE id = ?', 1);
        
        $this->assertEquals('SELECT * FROM actors WHERE id = 1', $q->toSQL());
    }
}