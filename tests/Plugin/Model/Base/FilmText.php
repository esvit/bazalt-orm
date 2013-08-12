<?php

namespace tests\Plugin\Model\Base;

/**
 * @codeCoverageIgnore
 */
abstract class FilmText extends \tests\Model\Base\Record
{
    const TABLE_NAME = 'film_text';

    const MODEL_NAME = 'tests\\Plugin\\Model\\FilmText';

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

    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\\ORM\\Plugin\\Serializable', 'description');
    }
}