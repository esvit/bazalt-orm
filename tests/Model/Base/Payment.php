<?php
/**
 * @codeCoverageIgnore
 */
abstract class tests\Model\Base_Payment extends tests\Model\Base_Record
{
    const TABLE_NAME = 'payment';

    const MODEL_NAME = 'tests\Model\Payment';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('payment_id', 'PUA:smallint(5)');
        $this->hasColumn('customer_id', 'U:smallint(5)');
        $this->hasColumn('staff_id', 'U:tinyint(3)');
        $this->hasColumn('rental_id', 'N:int(11)');
        $this->hasColumn('amount', 'decimal(5,2)');
        $this->hasColumn('payment_date', 'datetime');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('Customer', new ORM_Relation_One2One('tests\Model\Customer', 'customer_id',  'customer_id'));
        $this->hasRelation('Rental', new ORM_Relation_One2One('tests\Model\Rental', 'rental_id',  'rental_id'));
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