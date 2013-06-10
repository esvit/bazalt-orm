<?php
/**
 * @codeCoverageIgnore
 */
abstract class tests\Model\Base_Store extends tests\Model\Base_Record
{
    const TABLE_NAME = 'store';

    const MODEL_NAME = 'tests\Model\Store';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('store_id', 'PUA:tinyint(3)');
        $this->hasColumn('manager_staff_id', 'U:tinyint(3)');
        $this->hasColumn('address_id', 'U:smallint(5)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('Address', new ORM_Relation_One2One('tests\Model\Address', 'address_id',  'address_id'));
        $this->hasRelation('Staff', new ORM_Relation_One2Many('tests\Model\Staff', 'store_id', 'store_id'));
        $this->hasRelation('Customer', new ORM_Relation_One2Many('tests\Model\Customer', 'store_id', 'store_id'));
        $this->hasRelation('Inventory', new ORM_Relation_One2Many('tests\Model\Inventory', 'store_id', 'store_id'));
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