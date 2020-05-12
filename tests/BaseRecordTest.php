<?php

namespace tests;

class BaseRecordTest extends ORMBaseCase
{
    protected $testObj;

    protected $testObj2;

    protected function setUp():void
    {
        $this->testObj = new \tests\Model\Actor();
        $this->testObj->first_name = substr(mt_rand().time(),16);
        $this->testObj->last_name = '123456';
        $this->testObj->save();

        $this->testObj2 = new \tests\Model\Actor();
        $this->testObj2->first_name = substr(mt_rand().time(),16);
        $this->testObj2->last_name = 'qwerty';
        $this->testObj2->save();

        $film = new \tests\Model\Film();
        $film->title = 'Test film 1';
        $film->language_id = 1;

        $this->testObj->Films->add($film);
        $this->testObj2->Films->add($film);

        $film = new \tests\Model\Film();
        $film->title = 'Test film 2';
        $film->language_id = 2;

        $this->testObj->Films->add($film);
    }
    
    protected function tearDown():void
    {
        if (!is_null($this->testObj)) {
            $this->testObj->Films->removeAll();
            $this->testObj->delete();
        }
        if (!is_null($this->testObj2)) {
            $this->testObj2->Films->removeAll();
            $this->testObj2->delete();
        }
    }

    /**
     * @covers Bazalt\ORM\BaseRecord::__get
     */
    public function test__get()
    {
        $this->assertNull($this->testObj->dummy);

        // relations
        $films = $this->testObj->Films;
        $films2 = $this->testObj2->Films;

        $this->assertNotEquals($films->baseObject()->actor_id, $films2->baseObject()->actor_id);
    }

    /**
     * @covers Bazalt\ORM\BaseRecord::getField
     */
    public function testGetField()
    {
        $this->assertEquals($this->testObj2->getField('last_name'), 'qwerty');

        $this->assertNull($this->testObj2->getField('dummy'));
    }
}
