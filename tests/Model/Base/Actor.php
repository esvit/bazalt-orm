<?php

namespace tests\Model\Base;

/**
 * @codeCoverageIgnore
 */
abstract class Actor extends Record
{
    const TABLE_NAME = 'actor';

    const MODEL_NAME = 'tests\Model\Actor';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('actor_id', 'PUA:smallint(5)');
        $this->hasColumn('first_name', 'varchar(45)');
        $this->hasColumn('last_name', 'varchar(45)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('Films', new \Bazalt\ORM\Relation\Many2Many('tests\Model\Film', 'actor_id', 'tests\Model\FilmActor', 'film_id'));
    }
}