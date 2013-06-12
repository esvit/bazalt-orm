<?php

namespace tests\Model\Base;

/**
 * @codeCoverageIgnore
 */
abstract class Inventory extends Record
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
        $this->hasRelation('Film', new \Bazalt\ORM\Relation\One2One('tests\Model\Film', 'film_id',  'film_id'));
        $this->hasRelation('Rental', new \Bazalt\ORM\Relation\One2Many('tests\Model\Rental', 'inventory_id', 'inventory_id'));
        $this->hasRelation('Store', new \Bazalt\ORM\Relation\One2One('tests\Model\Store', 'store_id',  'store_id'));
    }
}