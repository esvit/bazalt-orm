<?php

namespace tests\Model\Base;

/**
 * @codeCoverageIgnore
 */
abstract class Store extends Record
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
        $this->hasRelation('Address', new \Bazalt\ORM\Relation\One2One('tests\Model\Address', 'address_id',  'address_id'));
        $this->hasRelation('Staff', new \Bazalt\ORM\Relation\One2Many('tests\Model\Staff', 'store_id', 'store_id'));
        $this->hasRelation('Customer', new \Bazalt\ORM\Relation\One2Many('tests\Model\Customer', 'store_id', 'store_id'));
        $this->hasRelation('Inventory', new \Bazalt\ORM\Relation\One2Many('tests\Model\Inventory', 'store_id', 'store_id'));
    }
}