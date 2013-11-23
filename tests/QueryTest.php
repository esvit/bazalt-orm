<?php

namespace tests;

class QueryTest extends ORMBaseCase
{
    protected $testObj;

    protected function setUp()
    {
    }
    
    protected function tearDown()
    {
    }

    /**
     * @covers Bazalt\ORM\Query::toSQL
     */
    public function testGetTable()
    {
        $q = new \Bazalt\ORM\Query('SELECT * FROM actors WHERE id = ?', array(1));
        
        $this->assertEquals('SELECT * FROM actors WHERE id = 1', $q->toSQL());

        $q = new \Bazalt\ORM\Query('SELECT * FROM actors WHERE id = ?', 1);
        
        $this->assertEquals('SELECT * FROM actors WHERE id = 1', $q->toSQL());
    }
}