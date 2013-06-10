<?php
/**
 * @codeCoverageIgnore
 */
abstract class tests\Model\Base_Rental extends tests\Model\Base_Record
{
    const TABLE_NAME = 'rental';

    const MODEL_NAME = 'tests\Model\Rental';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('rental_id', 'PA:int(11)');
        $this->hasColumn('rental_date', 'datetime');
        $this->hasColumn('inventory_id', 'U:mediumint(8)');
        $this->hasColumn('customer_id', 'U:smallint(5)');
        $this->hasColumn('return_date', 'N:datetime');
        $this->hasColumn('staff_id', 'U:tinyint(3)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('Customer', new ORM_Relation_One2One('tests\Model\Customer', 'customer_id',  'customer_id'));
        $this->hasRelation('Inventory', new ORM_Relation_One2One('tests\Model\Inventory', 'inventory_id',  'inventory_id'));
        $this->hasRelation('Payment', new ORM_Relation_One2Many('tests\Model\Payment', 'rental_id', 'rental_id'));
        $this->hasRelation('Staff', new ORM_Relation_One2One('tests\Model\Staff', 'staff_id',  'staff_id'));
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