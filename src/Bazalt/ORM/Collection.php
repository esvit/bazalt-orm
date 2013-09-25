<?php

namespace Bazalt\ORM;

class Collection
{
    /**
     * Поточний запит
     *
     * @var Query
     */
    protected $query = null;

    /**
     * Номер поточної сторінки
     *
     * @var int
     */
    protected $currentPage = 1;
    
    /**
     * К-ть записів на сторінку
     *
     * @var int
     */
    protected $countPerPage = 10;
    
    /**
     * К-ть сторінок
     *
     * @var int
     */
    protected $pagesCount = 0;
    
    /**
     * К-ть записів
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Construct
     *
     * @param Query $query Поточний запит
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }
    
    /**
     * Повертає к-ть сторінок
     *
     * @return int К-ть сторінок
     */
    public function getPagesCount()
    {
        return $this->pagesCount;
    }
    
    /**
     * Повертає к-ть записів
     * 
     * @deprecated
     *
     * @return int К-ть записів
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Повертає або встановлює к-ть записів
     *
     * @param int|null $count К-ть записів
     *
     * @return int К-ть записів
     */
    public function count($count = null)
    {
        if ($count != null) {
            $this->count = $count;
            return $this;
        }
        return $this->count;
    }

    /**
     * Повертає або встановлює к-ть записів на сторінку
     *
     * @param int|null $countPerPage К-ть записів на сторінку
     *
     * @return int К-ть записів на сторінку
     */
    public function countPerPage($countPerPage = null)
    {
        if ($countPerPage != null) {
            $this->countPerPage = $countPerPage;
            return $this;
        }
        return $this->countPerPage;
    }
    
    /**
     * Повертає або встановлює номер поточної сторінки
     *
     * @param int|null $page Номер поточної сторінки
     *
     * @return int Номер поточної сторінки
     */
    public function page($page = null)
    {
        if ($page !== null) {
            if ($page < 1) {
                $page = 1;
            }
            $this->currentPage = $page;
            return $this;
        }
        return $this->currentPage;
    }
    
    /**
     * Встановлює $this->currentPage, $this->countPerPage і робить вибірку данних для поточного запиту
     *
     * @param int $page      Поточна сторінка
     * @param int $countPerPage К-ть записів на сторінку
     *
     * @return array Результат вибірки
     */
    public function getPage($page = 1, $countPerPage = 10)
    {
        return $this->page((int)$page)
                    ->countPerPage((int)$countPerPage)
                    ->fetchPage();
    }

    /**
     * Робить вибірку данних для поточного запиту на основі $this->currentPage, $this->countPerPage і заповнює $this->count
     *
     * @return array Результат вибірки
     */
    public function fetchPage()
    {
        $curPage = $this->page();
        $q = clone $this->query;

        $this->count = $this->query->rowCount();
        $start = ($curPage-1) * $this->countPerPage;
        if ($this->count > 0 && $start >= $this->count) {
            throw new \Bazalt\ORM\Exception\Collection('Invalid page number');
        }
        $q = clone $this->query;
        $q->limit($start, $this->countPerPage);
        $this->pagesCount = ceil($this->count/$this->countPerPage);

        return $q->fetchAll();
    }

    /**
     * Формує запит, що рахує позиції елементів
     *
     * @return Query Запит
     */
    protected function getOrderQuery()
    {
        $q = clone $this->query;
        $q->from('(SELECT @num := 0) AS rowNumber')
          ->select('*, @num := @num + 1 AS number');

        return \Bazalt\ORM::select()->from($q);
    }

    /**
     * Дізнається позицію елементу у колекції
     *
     * @param Record $item Елемент
     *
     * @return int|null Позиція елементу у колекції
     */
    public function getItemOrder($item)
    {
        $newQuery = $this->getOrderQuery();
        $res = $newQuery->andWhere('id = ?', $item->id)
                        ->fetch('stdClass');

        return ($res) ? $res->number : null;
    }

    /**
     * Повертає елемент або масив елементів, який знаходиться після заданого елементу
     *
     * @param Record $item  Елемент
     * @param int        $limit К-сть елементів в результатів
     *
     * @return Record|array Наступний елемент(и)
     */
    public function getNext($item, $limit = 1)
    {
        $order = $this->getItemOrder($item);
        if (!$order) {
            return null;
        }
        $q = $this->getOrderQuery();
        $q->andWhere('number > ?', $order)
          ->orderBy('number ASC');

        if (is_numeric($limit)) {
            $q->limit($limit);
            return $q->fetch(get_class($item));
        }
        return $q->fetchAll(get_class($item));
    }

    /**
     * Повертає елемент або масив елементів, який знаходиться перед заданим елементом
     *
     * @param Record $item  Елемент
     * @param int        $limit К-сть елементів в результатів
     *
     * @return Record|array Попередній елемент(и)
     */
    public function getPrev($item, $limit = 1)
    {
        $order = $this->getItemOrder($item);
        if (!$order) {
            return null;
        }
        $q = $this->getOrderQuery();
        $q->andWhere('number < ?', $order)
          ->orderBy('number DESC');

        if (is_numeric($limit)) {
            $q->limit($limit);
            return $q->fetch(get_class($item));
        }
        return $q->fetchAll(get_class($item));
    }
    
    /**
     * Повертає масив, або асоціований масив зі значень $field1 або $field1 => $field2
     *
     * @param string      $field1 Назва поля
     * @param string|null $field2 Назва поля
     *
     * Example:
     * <code>
     * CMS_Model_User::getCollection()->toArray('id') --> array(1, 2, 3...)
     * CMS_Model_User::getCollection()->toArray('id', 'login') --> array(1 => 'test1', 2 => 'test2', 3 => 'test3'...)
     * </code>
     *
     * @return array Результат 
     */
    public function toArray($field1, $field2 = null)
    {
        $items = $this->query->fetchAll();
        $res = array();
        foreach($items as $item) {
            if($field2) {
                $res[$item->{$field1}] = $item->{$field2};
            } else {
                $res []= $item->{$field1};
            }
        }
        return $res;
    }

    /**
     * Проксі метод для запиту, прокидає виклик orderBy в об'єкт $this->query
     *
     * @param string $fields Список полів для ORDER BY
     *
     * @return Query Поточний запит
     */
    public function orderBy($fields)
    {
        return $this->query->orderBy($fields);
    }

    /**
     * Проксі метод для запиту, прокидає виклик addOrderBy в об'єкт $this->query
     *
     * @param string $fields Список полів для ORDER BY
     *
     * @return Query Поточний запит
     */
    public function addOrderBy($fields)
    {
        return $this->query->addOrderBy($fields);
    }
    
    /**
     * Проксі метод для запиту, прокидає виклик fetchAll в об'єкт $this->query
     *
     * @return array Результат вибірки 
     */
    public function fetchAll()
    {
        return $this->query->fetchAll();
    }
    
    /**
     * Проксі метод для запиту, прокидає виклик $name в об'єкт $this->query
     *
     * @param string $name      Ім'я методу
     * @param array  $arguments Список аргументів
     *
     * @return Query Поточний запит
     */
    public function __call($name, $arguments = array())
    {
        return call_user_func_array(array($this->query, $name), $arguments);
    }
}