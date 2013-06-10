<?php

namespace Model\Base;

abstract class Category extends \Framework\System\ORM\Record
{
    const TABLE_NAME = 'Category';

    const MODEL_NAME = 'Model\Category';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('category_id', 'PUA:int(10)');
        $this->hasColumn('name', 'varchar(25)');
        $this->hasColumn('last_update', 'timestamp');
    }

    public function initRelations()
    {
    }
    
    public function initPlugins()
    {
        $this->hasPlugin('Framework\System\ORM\Plugin\Timestampable', ['updated' => 'last_update']);
    }
}