<?php
/**
 * @codeCoverageIgnore
 */
abstract class tests\Model\Base_City extends tests\Model\Base_Record
{
    const TABLE_NAME = 'city';

    const MODEL_NAME = 'tests\Model\City';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('city_id', 'PUA:smallint(5)');
        $this->hasColumn('city', 'varchar(50)');
        $this->hasColumn('country_id', 'U:smallint(5)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('Address', new ORM_Relation_One2Many('tests\Model\Address', 'city_id', 'city_id'));
        $this->hasRelation('Country', new ORM_Relation_One2One('tests\Model\Country', 'country_id',  'country_id'));
    }
}