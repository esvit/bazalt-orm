<?php

namespace tests\Relation;

class One2One extends \tests\ORMBaseCase
{
    protected $testObj;

    protected function tearDown()
    {
        $this->testObj = \tests\Model\Address::getById(1);

        $newCity = \tests\Model\City::select()->where('city = ?', 'Lethbridge')->fetch();
        $this->testObj->City = $newCity;
    }

    public function testGet()
    {
        $this->testObj = \tests\Model\Address::getById(1);
        $this->assertEquals($this->testObj->City->city, 'Lethbridge');

        $this->testObj->city_id = null;
        $this->assertNull($this->testObj->City);
    }

    public function testSet()
    {
        $this->testObj = \tests\Model\Address::getById(1);

        $oldCity = $this->testObj->City;
        $newCity = \tests\Model\City::select()->where('city = ?', 'Abha')->fetch();

        $this->testObj->City = $newCity;
        $this->assertEquals($this->testObj->City->city, 'Abha');

        $this->testObj->City = $oldCity;
        $this->assertEquals($this->testObj->City->city, 'Lethbridge');

        $address = new \tests\Model\Address();
        $address->City = $oldCity;
        $this->assertEquals($address->City->city, 'Lethbridge');
    }
    
    public function testGetQuery()
    {
        $this->testObj = new \Bazalt\ORM\Relation\One2One('tests\Model\City', 'city_id',  'city_id');
        $base = \tests\Model\Address::getById(1);
        $this->testObj->baseObject($base);
        $q = $this->testObj->getQuery();
        $this->assertEquals($q->toSql(), 'SELECT * FROM city AS ft WHERE  (ft.city_id = "300") LIMIT 1');
    }
}