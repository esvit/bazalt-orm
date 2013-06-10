<?php
/**
 * @codeCoverageIgnore
 */
abstract class tests\Model\Base_Film extends tests\Model\Base_Record
{
    const TABLE_NAME = 'film';

    const MODEL_NAME = 'tests\Model\Film';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('film_id', 'PUA:smallint(5)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('description', 'N:text');
        $this->hasColumn('release_year', 'N:year(4)');
        $this->hasColumn('language_id', 'U:tinyint(3)');
        $this->hasColumn('original_language_id', 'UN:tinyint(3)');
        $this->hasColumn('rental_duration', 'U:tinyint(3)|3');
        $this->hasColumn('rental_rate', 'decimal(4,2)|4.99');
        $this->hasColumn('length', 'UN:smallint(5)');
        $this->hasColumn('replacement_cost', 'decimal(5,2)|19.99');
        $this->hasColumn('rating', 'N:enum("G","PG","PG-13","R","NC-17")|G');
        $this->hasColumn('special_features', 'STN:set("Trailers","Commentaries","Deleted)');
        $this->hasColumn('last_update', 'timestamp|CURRENT_TIMESTAMP');
    }

    public function initRelations()
    {
        $this->hasRelation('FilmActor', new ORM_Relation_One2Many('tests\Model\FilmActor', 'film_id', 'film_id'));
        $this->hasRelation('FilmCategory', new ORM_Relation_One2Many('tests\Model\FilmCategory', 'film_id', 'film_id'));
        $this->hasRelation('Inventory', new ORM_Relation_One2Many('tests\Model\Inventory', 'film_id', 'film_id'));
        $this->hasRelation('Language', new ORM_Relation_One2One('tests\Model\Language', 'original_language_id',  'language_id'));
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