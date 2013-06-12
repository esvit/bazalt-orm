<?php

namespace tests\Model\Base;

/**
 * @codeCoverageIgnore
 */
abstract class FilmCategory extends Record
{
    const TABLE_NAME = 'film_category';

    const MODEL_NAME = 'tests\Model\FilmCategory';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('film_id', 'PU:smallint(5)');
        $this->hasColumn('category_id', 'PU:tinyint(3)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('Category', new \Bazalt\ORM\Relation\One2One('tests\Model\Category', 'category_id',  'category_id'));
        $this->hasRelation('Film', new \Bazalt\ORM\Relation\One2One('tests\Model\Film', 'film_id',  'film_id'));
    }
}