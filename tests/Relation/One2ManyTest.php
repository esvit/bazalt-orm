<?php

require_once dirname(__FILE__) . '/../bootstrap.inc';

class ORM_Test_Relation_One2Many extends Tests\BaseCase
{
    protected $testObj;

    public function testGet()
    {
        $this->testObj = ORMTest_Model_Address::getById(3);
        $objs = $this->testObj->Staff->get();
        $this->assertEquals(count($objs), 1);
        $this->assertEquals($objs[0]->staff_id, 1);
    }
    
    public function testGetQuery()
    {
        $this->testObj = ORMTest_Model_Address::getById(3);
        $q = $this->testObj->Staff->getQuery();
        $this->assertEquals($q->toSql(), 'SELECT * FROM staff AS ft WHERE  (ft.address_id = "3") ');
    }
    
    public function testAdd()
    {
        $this->testObj = ORMTest_Model_City::getById(1);
        
        $addr = new ORMTest_Model_Address();
        $addr->address = 'some address';
        $addr->city_id = 10;
        $addr->save();
        
        $this->assertEquals($addr->city_id, 10);
        $this->testObj->Address->add($addr);
        $this->assertEquals($addr->city_id, 1);
        
        $addr->delete();
    }
    

    // public function testRemoveAll()
    // {
        // $this->testObj = City::getById(1);
        
        // $addr = new Address();
        // $addr->address = 'some address';
        // $addr->city_id = 10;
        // $addr->save();
        
        // $this->assertEquals($addr->city_id, 10);
        // $this->testObj->Address->add($addr);
        // $this->assertEquals($addr->city_id, 1);
        
        // $addr->delete();
    // }


    public function testHas()
    {
        $this->testObj = ORMTest_Model_City::getById(300);
        
        $this->assertTrue($this->testObj->Address->has(ORMTest_Model_Address::getById(1)));
        $this->assertFalse($this->testObj->Address->has(ORMTest_Model_Address::getById(2)));
    }
    
    #Exceptions
}