<?php

use Framework\System\ORM\ORM;

require_once 'bootstrap.inc';

class ORMTest_ORM extends Tests\BaseCase
{
    public function testSelectt()
    {
        $sql = ORM::select('ORMTest_Model_Actor', null)->toSQL();
        $this->assertEquals('SELECT * FROM actor AS f ', $sql);
        
        $sql = ORM::select('ORMTest_Model_Actor a', 'a.last_name')->toSQL();
        $this->assertEquals('SELECT a.last_name FROM actor AS a ', $sql);
    }

    public function testInsert()
    {
        $testObj = new ORMTest_Model_Actor();
        $testObj->last_name = '123456';
        
        $sql = ORM::insert('ORMTest_Model_Actor', $testObj)->toSQL();
        $this->assertEquals('INSERT INTO actor (`last_name`) VALUES ("123456")', $sql);
    }
    
    /**
     * @expectedException ORM_Exception_Insert
     */
    public function testInsertException()
    {
        $sql = ORM::insert(null, null)->toSQL();
    }

    public function testUnion()
    {
        $query1 = new ORM_Query_Select();
        $query1->from('ORMTest_Model_Actor');
        
        $query2 = new ORM_Query_Select();
        $query2->from('ORMTest_Model_Address');
        
        $union = ORM::union($query1, $query2);
        $sql = $union->toSQL();
        $this->assertEquals('(SELECT * FROM actor ) UNION DISTINCT (SELECT * FROM address )', $sql);
        
        $union = ORM::union($query1, $query2, ORM::UNION_ALL);
        $sql = $union->toSQL();
        $this->assertEquals('(SELECT * FROM actor ) UNION ALL (SELECT * FROM address )', $sql);
    }
    
    /*public function testGetModelClasses()
    {
        $arr = array (
            'ORMTest_Model_Actor',
            'ORMTest_Model_Address',
            'ORMTest_Model_Category',
            'ORMTest_Model_City',
            'ORMTest_Model_Country',
            'ORMTest_Model_Customer',
            'ORMTest_Model_Film',
            'ORMTest_Model_FilmActor',
            'ORMTest_Model_FilmCategory',
            'ORMTest_Model_FilmText',
            'ORMTest_Model_Inventory',
            'ORMTest_Model_Language',
            'ORMTest_Model_Payment',
            'ORMTest_Model_Rental',
            'ORMTest_Model_Staff',
            'ORMTest_Model_Store'
        );
    
        $this->assertEquals($arr, ORM::getModelClasses());
    }*/
}