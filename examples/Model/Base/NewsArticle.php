<?php

namespace Model\Base;

abstract class NewsArticle extends \Bazalt\ORM\Record
{
    const TABLE_NAME = 'categories';

    const MODEL_NAME = 'Model\NewsArticle';

    const ENGINE = 'InnoDB';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME, self::ENGINE);
    }

    protected function initFields()
    {
        $this->hasColumn('article_id', 'PUA:int(10)');
        $this->hasColumn('category_id', 'U:int(10)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('description', 'text');
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