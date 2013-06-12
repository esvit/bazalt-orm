<?php

namespace tests\Model\Base;

/**
 * @codeCoverageIgnore
 */
abstract class Category extends Record
{
    const TABLE_NAME = 'category';

    const MODEL_NAME = 'tests\Model\Category';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('category_id', 'PUA:tinyint(3)');
        $this->hasColumn('name', 'varchar(25)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('FilmCategory', new \Bazalt\ORM\Relation\One2Many('tests\Model\FilmCategory', 'category_id', 'category_id'));
    }
}