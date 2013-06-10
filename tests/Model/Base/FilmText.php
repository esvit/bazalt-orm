<?php
/**
 * @codeCoverageIgnore
 */
abstract class tests\Model\Base_FilmText extends tests\Model\Base_Record
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