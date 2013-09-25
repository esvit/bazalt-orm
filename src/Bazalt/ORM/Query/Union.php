<?php

namespace Bazalt\ORM\Query;

use Bazalt\ORM as ORM;

class Union extends Builder
{
    /**
     * Початкове значення ліміту
     *
     * @var integer
     */
    protected $limitFrom = null;

    /**
     * Кількість записів в результаті вибірки
     *
     * @var integer
     */
    protected $limitCount = null;

    /**
     * Масив полів, які ввійдуть у результат вибірки
     *
     * @var array
     */
    protected $select = array('*');
    
    /**
     * Масив ORDER BY параметрів
     *
     * @var array
     */
    protected $orderBy = array();
    
    /**
     * Тип об'єднання
     *
     * @var string
     */
    protected $type = ORM::UNION_DISTINCT;

    /**
     * Запит для об'єднання
     *
     * @var \Bazalt\ORM\Query\Builder
     */
    protected $query1 = null;

    /**
     * Запит для об'єднання
     *
     * @var \Bazalt\ORM\Query\Builder
     */
    protected $query2 = null;

    /**
     * Construct
     *
     * @param \Bazalt\ORM\Query\Builder $query1 Запит для об'єднання
     * @param \Bazalt\ORM\Query\Builder $query2 Запит для об'єднання
     */
    public function __construct(Builder $query1, Builder $query2)
    {
        parent::__construct();

        $this->query1 = $query1;
        $this->query2 = $query2;
        
        $p1 = $query1->getQueryParams();
        $p2 = $query2->getQueryParams();
        if (is_array($p1)) {
            $this->whereParams = array_merge($this->whereParams, $p1);
        } else {
            $this->whereParams []= $p1;
        }
        if (is_array($p2)) {
            $this->whereParams = array_merge($this->whereParams, $p2);
        } else {
            $this->whereParams []= $p2;
        }
    }

    /**
     * Встановлює тип запиту UNION ALL
     *
     * @return Union
     */
    public function all()
    {
        $this->type = ORM::UNION_ALL;
        return $this;
    }

    /**
     * Встановлює тип запиту UNION DISTINCT
     *
     * @return Union
     */
    public function distinct()
    {
        $this->type = ORM::UNION_DISTINCT;
        return $this;
    }

    /**
     * Встановлює поля, які ввійдуть у результат вибірки
     *
     * @param array $fields Масив полів, які ввійдуть у результат вибірки
     *
     * @return Union
     */
    public function select($fields)
    {
        $this->select = self::explode($fields);
        return $this;
    }

    /**
     * Генерує SQL для запиту
     *
     * @return string
     */
    public function buildSQL()
    {
        $query  = '(' . $this->query1->toSQL() . ')';
        $query .= ' UNION ' . $this->type . ' ';
        $query .= '(' . $this->query2->toSQL() . ')';

        if (count($this->orderBy) > 0) {
            $query .= ' ORDER BY ' . implode(',', $this->orderBy) . ' ';
        }

        if (isset($this->limitFrom)) {
            $query .= ' LIMIT ' . $this->limitFrom . (isset($this->limitCount) ? ', '.$this->limitCount : '' );
        }
        return $query;
    }

    /**
     * Повертає масив тегів кешу для запиту
     *
     * @return array Масив тегів
     */
    protected function getCacheTags()
    {
        return array_merge($this->query1->getCacheTags(), $this->query2->getCacheTags());
    }

    /**
     * Встановлює ліміт для запиту
     *
     * @param integer $from  Початкове значення ліміту
     * @param integer $count Кількість записів в результаті
     *
     * @return Union
     */
    public function limit($from, $count = null)
    {
        if (!is_numeric($from) || (!is_null($count) && !is_numeric($count))) {
            throw new InvalidArgumentException();
        }
        $this->limitFrom = $from;
        $this->limitCount = $count;
        return $this;
    }

    /**
     * Додає ORDER BY параметри до запиту
     *
     * @param string $fields Список полів для ORDER BY
     *
     * @return Union
     */
    public function orderBy($fields)
    {
        $this->orderBy = self::explode($fields);
        foreach ($this->orderBy as &$orderField) {
            $orderFields = explode(' ', $orderField);
            if (count($orderFields) == 1) {
                $orderField = '`' . $orderField . '`';
            } else {
                $orderField = '`' . $orderFields[0] . '` ' . $orderFields[1];
            }
        }
        return $this;
    }
}