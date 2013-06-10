<?php
use Framework\System\ORM as ORM;
use Framework\System\ORM\Record;
use Framework\System\ORM\Plugin\AbstractPlugin;

require_once 'bootstrap.inc';

class ORMTest_Record extends Tests\BaseCase
{
    protected $testObj;

    protected function setUp()
    {
        $this->testObj = new ORMTest_Model_Actor();
        $this->testObj->first_name = substr(mt_rand().time(),16);
        $this->testObj->last_name = '123456';
        
        $builder = ORM\ORM::insert('ORMTest_Model_Actor');
        $builder->set($this->testObj)
                ->exec();
        $this->testObj->actor_id = $builder->Connection->getLastInsertId();
    }
    
    protected function tearDown()
    {
        if( !is_null($this->testObj) ) {
            $this->testObj->delete();

            $this->testObj->removeColumn('testColumn');
            $this->testObj->removeColumn('testColumn1');
        }
    }

    /**
     * @covers Framework\System\ORM\BaseRecord::getTable
     */
    public function testGetTable()
    {
        $t = ORM\BaseRecord::getTable('ORMTest_Model_Actor');
        
        $this->assertEquals('ORMTest_Model_Actor', get_class($t));
    }

    /**
     * @covers Framework\System\ORM\BaseRecord::getTableName
     */
    public function testGetTableName()
    {
        $tableName = ORM\BaseRecord::getTableName(get_class($this->testObj));
        $this->assertEquals('actor', $tableName);
    }

    /**
     * @covers Framework\System\ORM\BaseRecord::getTableName
     * @expectedException ORM_Exception_Table
     */
    public function testGetTableNameException()
    {
        $tableName = ORM\BaseRecord::getTableName('test');
        $this->assertEquals($tableName, null);
    }
    
    public function testGetAllColumns()
    {
        $tableName = ORM\BaseRecord::getTableName(get_class($this->testObj));
        $colums = ORM\BaseRecord::getAllColumns($tableName);

        $this->assertTrue(array_key_exists('actor_id', $colums));
        $this->assertTrue(array_key_exists('first_name', $colums));
        $this->assertTrue(array_key_exists('last_name', $colums));
        $this->assertTrue(array_key_exists('last_update', $colums));
    }
    
    /**
     * @expectedException ORM_Exception_Table
     */
    public function testGetAllColumnException()
    {
        ORM\BaseRecord::getAllColumns('notExistsModel');
    }
    
    public function testGetPrimaryKeys()
    {
        $tableName = get_class($this->testObj);
        $name = 'actor_id';
        $pks = ORM\BaseRecord::getPrimaryKeys($tableName);
        $this->assertEquals($name, $pks[$name]->name());
    }
    
    public function testGetAutoIncrementColumn()
    {
        $tableName = get_class($this->testObj);
        $this->assertEquals('actor_id', $this->testObj->getAutoIncrementColumn($tableName)->name());
    }
    
    public function testGetAutoIncrementValue()
    {
        $this->assertEquals($this->testObj->actor_id, $this->testObj->getAutoIncrementValue());
    }
    
    public function test__set()
    {
        $fieldName = 'aaa';
        $this->testObj->$fieldName = 132;
        
        $this->assertEquals(132, $this->testObj->getField($fieldName));
        $setted = $this->testObj->getSettedFields();
        $this->assertTrue($setted[$fieldName]);
    }
    
    public function test__get()
    {
        $fieldName = 'aaa';
        $this->testObj->$fieldName = 132;
        $this->assertEquals(132, $this->testObj->$fieldName);
        
        $name = 'testColumn11';
        $this->testObj->hasColumn($name, 'P:int');
        $this->assertEquals(null, $this->testObj->$name);
        
        $name = 'testColumn22';
        $this->testObj->hasColumn($name, 'P:int|10');
        $this->assertEquals(10, $this->testObj->$name);

        $this->testObj->removeColumn('testColumn11');
        $this->testObj->removeColumn('testColumn22');
    }
    
    public function testHasColumn()
    {
        $name = 'testColumn';
        $this->testObj->hasColumn($name, 'P:int');
        
        $this->assertTrue(array_key_exists($name, $this->testObj->getColumns()));
        
        $tableName = get_class($this->testObj);
        $this->assertEquals('actor_id', $this->testObj->getAutoIncrementColumn($tableName)->name());
        $pks = ORM\BaseRecord::getPrimaryKeys($tableName);
        $this->assertEquals($name, $pks[$name]->name());

        $this->testObj->removeColumn($name);
    }

    /**
     * @expectedException ORM_Exception_Table
     */
    public function testHasAIColumnException()
    {
        $this->testObj->hasColumn('testColumn1', 'PA:int');
        $this->testObj->hasColumn('testColumn2', 'PA:int');

        $this->testObj->removeColumn('testColumn1');
        $this->testObj->removeColumn('testColumn2');
    }

    public function testHasColumnFalse()
    {
        $this->assertTrue($this->testObj->hasColumn('testColumn', null));
        $this->assertFalse($this->testObj->hasColumn('testColumn', null));
    }
    
    public function testHasRelation()
    {
        $name = 'testRelation';
        $this->testObj->hasRelation($name, new ORM_Relation_One2One('test', 'test', 'test'));
        
        $this->assertTrue(array_key_exists($name, $this->testObj->getReferences()));
    }
    
    /**
     * @expectedException ORM_Exception_Table
     */
    public function testHasRelationException()
    {
        $this->testObj->hasRelation('testRelation', new ORM_Relation_One2One('test', 'test', 'test'));
        $this->testObj->hasRelation('testRelation', new ORM_Relation_One2One('test', 'test', 'test'));
    }
    
    public function testHasPlugin()
    {
        $options = array('one' => 'two');
        $name = 'TestORMPlugin';
        $this->testObj->hasPlugin($name, $options);
        
        $plugins = $this->testObj->getPlugins();
        $this->assertTrue(array_key_exists($name, $plugins));
        $this->assertEquals($options, $plugins[$name]);
    }
     
    public function test__isset()
    {
        $fieldName = 'aaa';
        $this->assertFalse(isset($this->testObj->$fieldName));
        $this->testObj->$fieldName = 132;
        $this->assertTrue(isset($this->testObj->$fieldName));
    }
    
    public function testExists()
    {
        $this->testObj->hasColumn('testColumn', 'P:int');

        $this->assertFalse($this->testObj->exists('aaa'));
        $this->assertTrue($this->testObj->exists('testColumn'));

        $this->testObj->removeColumn('testColumn');
    }
    
    public function test__unset()
    {
        $fieldName = 'aaa';
        $this->testObj->$fieldName = 132;
        unset($this->testObj->$fieldName);
        
        $this->assertFalse(array_key_exists($fieldName, $this->testObj->getFieldsValues()));
        $this->assertFalse(array_key_exists($fieldName, $this->testObj->getSettedFields()));
    }
    
    public function testFromArray()
    {
        $arr = array();        
        $arr['actor_id'] = $this->testObj->actor_id;
        $arr['first_name'] = substr(mt_rand().time(),16);
        $arr['last_name'] = '147852369';
        
        $this->testObj->fromArray($arr);
        
        $this->assertEquals($this->testObj->actor_id, $arr['actor_id']);
        $this->assertEquals($this->testObj->first_name, $arr['first_name']);
        $this->assertEquals($this->testObj->last_name, $arr['last_name']);
        
        $setted = $this->testObj->getSettedFields();
        $this->assertTrue($setted['actor_id']);
        $this->assertTrue($setted['first_name']);
        $this->assertTrue($setted['last_name']);
    }
    
    public function testToArray()
    {
        $arr = $this->testObj->toArray();
        foreach($this->testObj->getColumns() as $columns) {
            $fieldName = $columns->name();
            $this->assertArrayHasKey($fieldName, $arr);
            $this->assertEquals($this->testObj->$fieldName, $arr[$fieldName]);
        }
    }
}


class TestORMPlugin extends AbstractPlugin
{
    public static $inited = false;
    public static $initedFields = false;
    public static $initedRelations = false;
    public static $initedPlugins = false;

    public function init(Record $model, $options)
    {
        self::$inited = true;
    }
    
    public function initFields(Record $model, $options)
    {
        self::$initedFields = true;
    }
    
    public function initRelations($model, $options)
    {
        self::$initedRelations = true;
    }
    
    public function initPlugins($model, $options)
    {
        self::$initedPlugins = true;
    }
    
}