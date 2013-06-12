<?php

namespace tests\Model\Base;

/**
 * @codeCoverageIgnore
 */
abstract class FilmText extends Record
{
    const TABLE_NAME = 'film_text';

    const MODEL_NAME = 'tests\Model\FilmText';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('film_id', 'P:smallint(6)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('description', 'N:text');
    }

    public function initRelations()
    {
    }
}