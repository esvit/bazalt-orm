<?php

namespace Model\Base;

abstract class Category extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'categories';

    const MODEL_NAME = 'Model\Category';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('category_id', 'PUA:int(10)');
        $this->hasColumn('name', 'varchar(255)');
        $this->hasColumn('created_at', 'datetime');
        $this->hasColumn('updated_at', 'datetime');
    }

    public function initRelations()
    {
    }
    
    public function initPlugins()
    {
        $this->hasPlugin('Bazalt\ORM\Plugin\Timestampable', [
            'created' => 'created_at',
            'updated' => 'updated_at'
        ]);
    }
}