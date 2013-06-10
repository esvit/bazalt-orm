<?php

require_once dirname(__FILE__). '/../bootstrap.inc';

class ORM_Test_Relation_One2One extends Tests\BaseCase
{
    protected $testObj;

    protected function tearDown()
    {
        $this->testObj = ORMTest_Model_Address::getById(1);

        $newCity = ORMTest_Model_City::select()->where('city = ?', 'Lethbridge')->fetch();
        $this->testObj->City = $newCity;
    }

    public function testGet()
    {
        $this->testObj = ORMTest_Model_Address::getById(1);
        $this->assertEquals($this->testObj->City->city, 'Lethbridge');

        $this->testObj->city_id = null;
        $this->assertNull($this->testObj->City);
    }

    public function testSet()
    {
        $this->testObj = ORMTest_Model_Address::getById(1);

        $oldCity = $this->testObj->City;
        $newCity = ORMTest_Model_City::select()->where('city = ?', 'Abha')->fetch();

        $this->testObj->City = $newCity;
        $this->assertEquals($this->testObj->City->city, 'Abha');

        $this->testObj->City = $oldCity;
        $this->assertEquals($this->testObj->City->city, 'Lethbridge');

        $address = new ORMTest_Model_Address();
        $address->City = $oldCity;
        $this->assertEquals($address->City->city, 'Lethbridge');
    }
    
    public function testGetQuery()
    {
        $this->testObj = new ORM_Relation_One2One('ORMTest_Model_City', 'city_id',  'city_id');
        $base = ORMTest_Model_Address::getById(1);
        $this->testObj->setBaseObject($base);
        $q = $this->testObj->getQuery();
        $this->assertEquals($q->toSql(), 'SELECT * FROM city AS ft WHERE  (ft.city_id = "300") LIMIT 1');
    }
}

