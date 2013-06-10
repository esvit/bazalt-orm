<?php
/**
 * @codeCoverageIgnore
 */
abstract class ORMTest_Model_Base_Address extends ORMTest_Model_Base_Record
{
    const TABLE_NAME = 'address';

    const MODEL_NAME = 'ORMTest_Model_Address';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('address_id', 'PUA:smallint(5)');
        $this->hasColumn('address', 'varchar(50)');
        $this->hasColumn('address2', 'N:varchar(50)');
        $this->hasColumn('district', 'varchar(20)');
        $this->hasColumn('city_id', 'U:smallint(5)');
        $this->hasColumn('postal_code', 'N:varchar(10)');
        $this->hasColumn('phone', 'varchar(20)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('Customer', new ORM_Relation_One2Many('ORMTest_Model_Customer', 'address_id', 'address_id'));
        $this->hasRelation('Staff', new ORM_Relation_One2Many('ORMTest_Model_Staff', 'address_id', 'address_id'));
        $this->hasRelation('Store', new ORM_Relation_One2Many('ORMTest_Model_Store', 'address_id', 'address_id'));
        $this->hasRelation('City', new ORM_Relation_One2One('ORMTest_Model_City', 'city_id',  'city_id'));
    }

    public static function getById($id)
    {
        return parent::getRecordById($id, self::MODEL_NAME);
    }

    public static function getAll($limit = null)
    {
        return parent::getAllRecords($limit, self::MODEL_NAME);
    }

    public static function select($fields = null)
    {
        return ORM::select(self::MODEL_NAME, $fields);
    }

    public static function insert($fields = null)
    {
        return ORM::insert(self::MODEL_NAME, $fields);
    }
}