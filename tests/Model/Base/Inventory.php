<?php
/**
 * @codeCoverageIgnore
 */
abstract class ORMTest_Model_Base_Inventory extends ORMTest_Model_Base_Record
{
    const TABLE_NAME = 'inventory';

    const MODEL_NAME = 'ORMTest_Model_Inventory';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('inventory_id', 'PUA:mediumint(8)');
        $this->hasColumn('film_id', 'U:smallint(5)');
        $this->hasColumn('store_id', 'U:tinyint(3)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('Film', new ORM_Relation_One2One('ORMTest_Model_Film', 'film_id',  'film_id'));
        $this->hasRelation('Rental', new ORM_Relation_One2Many('ORMTest_Model_Rental', 'inventory_id', 'inventory_id'));
        $this->hasRelation('Store', new ORM_Relation_One2One('ORMTest_Model_Store', 'store_id',  'store_id'));
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