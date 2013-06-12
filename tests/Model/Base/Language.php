<?php

namespace tests\Model\Base;

/**
 * @codeCoverageIgnore
 */
abstract class Language extends Record
{
    const TABLE_NAME = 'language';

    const MODEL_NAME = 'tests\Model\Language';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('language_id', 'PUA:tinyint(3)');
        $this->hasColumn('name', 'char(20)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('Film', new \Bazalt\ORM\Relation\One2Many('tests\Model\Film', 'language_id', 'original_language_id'));
    }
}