<?php
/**
 * @codeCoverageIgnore
 */
abstract class tests\Model\Base_Inventory extends tests\Model\Base_Record
{
    const TABLE_NAME = 'inventory';

    const MODEL_NAME = 'tests\Model\Inventory';

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
        $this->hasRelation('Film', new ORM_Relation_One2One('tests\Model\Film', 'film_id',  'film_id'));
        $this->hasRelation('Rental', new ORM_Relation_One2Many('tests\Model\Rental', 'inventory_id', 'inventory_id'));
        $this->hasRelation('Store', new ORM_Relation_One2One('tests\Model\Store', 'store_id',  'store_id'));
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