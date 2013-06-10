<?php
/**
 * @codeCoverageIgnore
 */
abstract class ORMTest_Model_Base_FilmActor extends ORMTest_Model_Base_Record
{
    const TABLE_NAME = 'film_actor';

    const MODEL_NAME = 'ORMTest_Model_FilmActor';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('actor_id', 'PU:smallint(5)');
        $this->hasColumn('film_id', 'PU:smallint(5)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('Actor', new ORM_Relation_One2One('ORMTest_Model_Actor', 'actor_id',  'actor_id'));
        $this->hasRelation('Film', new ORM_Relation_One2One('ORMTest_Model_Film', 'film_id',  'film_id'));
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