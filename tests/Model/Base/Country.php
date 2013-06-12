<?php

namespace tests\Model\Base;

/**
 * @codeCoverageIgnore
 */
abstract class Country extends Record
{
    const TABLE_NAME = 'country';

    const MODEL_NAME = 'tests\Model\Country';

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
        $this->hasRelation('City', new \Bazalt\ORM\Relation\One2Many('tests\Model\City', 'country_id', 'country_id'));
    }
}