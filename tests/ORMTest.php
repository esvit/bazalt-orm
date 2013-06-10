<?php

use Framework\System\ORM\ORM;

require_once 'bootstrap.inc';

class ORMTest_ORM extends Tests\BaseCase
{
    public function testSelectt()
    {
        $sql = ORM::select('tests\Model\Actor', null)->toSQL();
        $this->assertEquals('SELECT * FROM actor AS f ', $sql);
        
        $sql = ORM::select('tests\Model\Actor a', 'a.last_name')->toSQL();
        $this->assertEquals('SELECT a.last_name FROM actor AS a ', $sql);
    }

    public function testInsert()
    {
        $testObj = new tests\Model\Actor();
        $testObj->last_name = '123456';
        
        $sql = ORM::insert('tests\Model\Actor', $testObj)->toSQL();
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
        $query1->from('tests\Model\Actor');
        
        $query2 = new ORM_Query_Select();
        $query2->from('tests\Model\Address');
        
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
            'tests\Model\Actor',
            'tests\Model\Address',
            'tests\Model\Category',
            'tests\Model\City',
            'tests\Model\Country',
            'tests\Model\Customer',
            'tests\Model\Film',
            'tests\Model\FilmActor',
            'tests\Model\FilmCategory',
            'tests\Model\FilmText',
            'tests\Model\Inventory',
            'tests\Model\Language',
            'tests\Model\Payment',
            'tests\Model\Rental',
            'tests\Model\Staff',
            'tests\Model\Store'
        );
    
        $this->assertEquals($arr, ORM::getModelClasses());
    }*/
}