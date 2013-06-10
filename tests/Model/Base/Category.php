<?php
/**
 * @codeCoverageIgnore
 */
abstract class tests\Model\Base_Category extends tests\Model\Base_Record
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
        $this->hasRelation('FilmCategory', new ORM_Relation_One2Many('tests\Model\FilmCategory', 'category_id', 'category_id'));
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