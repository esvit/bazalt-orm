<?php

namespace Bazalt\ORM\Query;

trait Fetchable
{
    /**
     * Повертає масив результатів вибірки, якщо задано $this->pageNum рахує загальну к-ть записів
     *
     * @param string $baseClass Назва моделі
     *
     * @throws \Exception
     * @return array
     */
    public function fetchAll($baseClass = null)
    {
        if ($this->pageNum != null) {
            $query = new \ORM_Query('SELECT found_rows() AS `count` -- ' . implode(',', $this->getCacheTags()), array(), $this->getCacheTags());

            $pageCount = $query->fetch();

            $this->totalCount = $pageCount->count;
        }

        if ($baseClass != null) {
            return parent::fetchAll($baseClass);
        }
        if ($this->fetchType == null) {
            throw new \Exception('Unknown fetch type');
        }
        return parent::fetchAll($this->fetchType);
    }

    /**
     * Повертає один результат вибірки
     *
     * @param string $baseClass Назва моделі
     *
     * @throws \Exception
     * @return mixed
     */
    public function fetch($baseClass = null)
    {
        if ($baseClass != null) {
            $this->fetchType = $baseClass;
            //return parent::fetch($baseClass);
        }
        if ($this->fetchType == null || !class_exists($this->fetchType)) {
            throw new \Exception('Unknown fetch type "' . $this->fetchType . '"');
        }
        return parent::fetch($this->fetchType);
    }
}