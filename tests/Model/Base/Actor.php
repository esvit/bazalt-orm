<?php
/**
 * @codeCoverageIgnore
 */
abstract class tests\Model\Base_Actor extends tests\Model\Base_Record
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
        $this->hasRelation('Films', new ORM_Relation_Many2Many('tests\Model\Film', 'actor_id', 'tests\Model\FilmActor', 'film_id'));
    }

    public static function getById($id)
    {
        return parent::getRecordById($id, self::MODEL_NAME);
    }

    public static function getAll($limit = null)
    {
        return parent::getAllRecords($limit, self::MODEL_NAME);
    }

    public static function select($fields = null)
    {
        return ORM::select(self::MODEL_NAME, $fields);
    }

    public static function insert($fields = null)
    {
        return ORM::insert(self::MODEL_NAME, $fields);
    }
}