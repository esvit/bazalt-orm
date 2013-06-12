<?php

namespace tests\Model\Base;

/**
 * @codeCoverageIgnore
 */
abstract class Staff extends Record
{
    const TABLE_NAME = 'staff';

    const MODEL_NAME = 'tests\Model\Staff';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('staff_id', 'PUA:tinyint(3)');
        $this->hasColumn('first_name', 'varchar(45)');
        $this->hasColumn('last_name', 'varchar(45)');
        $this->hasColumn('address_id', 'U:smallint(5)');
        $this->hasColumn('picture', 'N:blob');
        $this->hasColumn('email', 'N:varchar(50)');
        $this->hasColumn('store_id', 'U:tinyint(3)');
        $this->hasColumn('active', 'tinyint(1)|1');
        $this->hasColumn('username', 'varchar(16)');
        $this->hasColumn('password', 'N:varchar(40)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('Address', new \Bazalt\ORM\Relation\One2One('tests\Model\Address', 'address_id',  'address_id'));
        $this->hasRelation('Payment', new \Bazalt\ORM\Relation\One2Many('tests\Model\Payment', 'staff_id', 'staff_id'));
        $this->hasRelation('Rental', new \Bazalt\ORM\Relation\One2Many('tests\Model\Rental', 'staff_id', 'staff_id'));
        $this->hasRelation('Store', new \Bazalt\ORM\Relation\One2One('tests\Model\Store', 'store_id',  'store_id'));
    }
}