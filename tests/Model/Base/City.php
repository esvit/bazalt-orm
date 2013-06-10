<?php
/**
 * @codeCoverageIgnore
 */
abstract class ORMTest_Model_Base_City extends ORMTest_Model_Base_Record
{
    const TABLE_NAME = 'city';

    const MODEL_NAME = 'ORMTest_Model_City';

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
        $this->hasRelation('Address', new ORM_Relation_One2Many('ORMTest_Model_Address', 'city_id', 'city_id'));
        $this->hasRelation('Country', new ORM_Relation_One2One('ORMTest_Model_Country', 'country_id',  'country_id'));
    }
}