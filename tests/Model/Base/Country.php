<?php
/**
 * @codeCoverageIgnore
 */
abstract class ORMTest_Model_Base_Country extends ORMTest_Model_Base_Record
{
    const TABLE_NAME = 'country';

    const MODEL_NAME = 'ORMTest_Model_Country';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('country_id', 'PUA:smallint(5)');
        $this->hasColumn('country', 'varchar(50)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('City', new ORM_Relation_One2Many('ORMTest_Model_City', 'country_id', 'country_id'));
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