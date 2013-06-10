<?php

require_once 'bootstrap.inc';

class ORMTest_Column extends Tests\BaseCase
{
    protected $testObj;

    public function testIsPrimaryKey()
    {
        $this->testObj = new ORM_Column('name', 'UA:int(10)');
        $this->assertFalse($this->testObj->isPrimaryKey());
        
        $this->testObj = new ORM_Column('name', 'PUA:int(10)');
        $this->assertTrue($this->testObj->isPrimaryKey());
    }
    
    public function testIsAutoIncrement()
    {
        $this->testObj = new ORM_Column('name', 'U:int(10)');
        $this->assertFalse($this->testObj->isAutoIncrement());
        
        $this->testObj = new ORM_Column('name', 'UA:int(10)');
        $this->assertTrue($this->testObj->isAutoIncrement());
    }
    
    public function testIsUnsigned()
    {
        $this->testObj = new ORM_Column('name', 'A:int(10)');
        $this->assertFalse($this->testObj->isUnsigned());
        
        $this->testObj = new ORM_Column('name', 'UA:int(10)');
        $this->assertTrue($this->testObj->isUnsigned());
    }
    
    public function testIsNullable()
    {
        $this->testObj = new ORM_Column('name', 'U:int(10)');
        $this->assertFalse($this->testObj->isNullable());
        
        $this->testObj = new ORM_Column('name', 'UN:int(10)');
        $this->assertTrue($this->testObj->isNullable());
    }
    
    public function testHasDefault()
    {
        $this->testObj = new ORM_Column('name', 'U:int(10)');
        $this->assertFalse($this->testObj->hasDefault());
        
        $this->testObj = new ORM_Column('name', 'UN:int(10)|2');
        $this->assertTrue($this->testObj->hasDefault());
    }
    
    public function testGetDefault()
    {
        $this->testObj = new ORM_Column('name', 'U:int(10)');
        $this->assertEquals(null, $this->testObj->getDefault());
        
        $this->testObj = new ORM_Column('name', 'UN:int(10)|2');
        $this->assertEquals(2,$this->testObj->getDefault());
    }
}