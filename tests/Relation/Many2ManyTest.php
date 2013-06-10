<?php

require_once dirname(__FILE__) . '/../bootstrap.inc';

class ORM_Test_Relation_Many2Many extends Tests\BaseCase
{
    protected $testObj;

    public function testGet()
    {
        $this->testObj = new ORMTest_Model_Actor();
        $this->assertEquals(count($this->testObj->Films->get()), 0);
        
        $this->testObj = ORMTest_Model_Actor::getById(1);
        $this->assertEquals(count($this->testObj->Films->get()), 19);
    }
    
    public function testGetQuery()
    {
        $this->testObj = ORMTest_Model_Actor::getById(1);
        $q = $this->testObj->Films->getQuery();
        $this->assertEquals($q->toSql(), 'SELECT * FROM film AS ft  INNER JOIN film_actor AS ref ON ref.film_id = ft.film_id WHERE  (ref.actor_id = 1) ');
    }
    
    public function testGetById()
    {
        $this->testObj = ORMTest_Model_Actor::getById(1);
        $film = $this->testObj->Films->getById(1);
        $this->assertEquals($film->title, 'ACADEMY DINOSAUR');
    }
    
    public function testCount()
    {
        $this->testObj = ORMTest_Model_Actor::getById(1);
        $this->assertEquals($this->testObj->Films->count(), 19);
    }

    public function testHasAddRemove()
    {
        $this->testObj = ORMTest_Model_Actor::getById(1);
        $film = ORMTest_Model_Film::getById(2);
        $this->assertFalse($this->testObj->Films->has($film));
        $this->testObj->Films->add($film);
        $this->assertTrue($this->testObj->Films->has($film));
        $this->testObj->Films->remove($film);
        $this->assertFalse($this->testObj->Films->has($film));
    }
    
    public function testRemoveAll()
    {
        $this->testObj = new ORMTest_Model_Actor();
        $this->testObj->first_name = 'first_name'.time();
        $this->testObj->last_name = 'last_name'.time();
        $this->testObj->save();

        $film1 = ORMTest_Model_Film::getById(2);
        $film2 = ORMTest_Model_Film::getById(3);
        
        $this->assertFalse($this->testObj->Films->has($film1));
        $this->assertFalse($this->testObj->Films->has($film2));
        
        $this->testObj->Films->add($film1);
        $this->testObj->Films->add($film2);
        
        $this->assertTrue($this->testObj->Films->has($film1));
        $this->assertTrue($this->testObj->Films->has($film2));
        

        $this->testObj->Films->removeAll();
        $this->assertFalse($this->testObj->Films->has($film1));
        $this->assertFalse($this->testObj->Films->has($film2));
        $this->testObj->delete();
    }
    
    public function testClearRelations()
    {
        $this->testObj = new ORMTest_Model_Actor();
        $this->testObj->first_name = 'first_nam'.time();
        $this->testObj->last_name = 'last_nam'.time();
        $this->testObj->save();
        
        $film1 = ORMTest_Model_Film::getById(2);
        $film2 = ORMTest_Model_Film::getById(3);
        
        $this->testObj->Films->add($film1);
        $this->testObj->Films->add($film2);
        
        $this->assertTrue($this->testObj->Films->has($film1));
        $this->assertTrue($this->testObj->Films->has($film2));
        
        $this->testObj->Films->clearRelations(array(2));
        
        $this->assertTrue($this->testObj->Films->has($film1));
        $this->assertFalse($this->testObj->Films->has($film2));

        $this->testObj->Films->removeAll();
        $this->testObj->delete();
    }
    
    public function testClearByRelations()
    {
        $this->testObj = new ORMTest_Model_Actor();
        $this->testObj->first_name = 'first_na'.time();
        $this->testObj->last_name = 'last_na'.time();
        $this->testObj->save();
        
        $film1 = new ORMTest_Model_Film();
        $film1->title = 'Film'.time();
        $film1->language_id = 1;
        $film1->save();
        
        $film2 = new ORMTest_Model_Film();
        $film2->title = 'Filmo'.time();
        $film2->language_id = 1;
        $film2->save();
        
        $this->testObj->Films->add($film1);
        $this->testObj->Films->add($film2);
        $this->assertTrue($this->testObj->Films->has($film1));
        $this->assertTrue($this->testObj->Films->has($film2));
        
        $this->testObj->Films->clearByRelations();
        
        $this->assertFalse($this->testObj->Films->has($film1));
        $this->assertFalse($this->testObj->Films->has($film2));
        $found = ORMTest_Model_Film::getById((int)$film1->film_id);
        $this->assertTrue($found == null);
        $found = ORMTest_Model_Film::getById((int)$film2->film_id);
        $this->assertTrue($found == null);
        
        $this->testObj->delete();
    }
    #Exceptions
}