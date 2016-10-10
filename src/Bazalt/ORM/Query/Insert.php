<?php
/**
 * Insert.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Query
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM\Query;

use Bazalt\ORM as ORM;

/**
 * Insert
 * Генерує INSERT запит до БД
 *
 * @category   System
 * @package    ORM
 * @subpackage Query
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Insert extends Builder
{
    /**
     * Якщо true - запит буде оновлювати дані на сервері, якщо такий запис вже існує
     *
     * @var array
     */
    protected $onDupicateUpdate = false;

    /**
     * Використовується для генерації ON DUPICATE UPDATE
     *
     * @var array
     */
    protected $dupicateParamsUsed = false;

    /**
     * Генерує запит щоб він оновлював дані на сервері, якщо такий запис вже існує
     *
     * @return \Bazalt\ORM\Query\Builder
     */
    public function onDupicateUpdate()
    {
        $this->onDupicateUpdate = true;
        return $this;
    }
    
    /**
     * Повертає масив параметрів для запиту
     *
     * @return array
     */
    protected function getQueryParams()
    {
        return $this->setParams;
    }
    
    /**
     * Повертає список таблиць і аліасів для запиту
     *
     * @return string 
     */
    protected function getFrom()
    {
        if ($this->from == 'DUAL' || !$this->from) {
            throw new ORM\Exception\Insert('INTO parameter not set', $this);
        }
        return parent::getFrom();
    }
    
    /**
     * Генерує SQL для запиту
     *
     * @return string
     */
    public function buildSQL()
    {
        ORM::cache()->removeByTags($this->getCacheTags());

        //if (!is_array($this->fields)) {
        //  throw new InsertBuilderException('No fields for insert');
        //}
        $query  = 'INSERT INTO ' . $this->getFrom();
        if (count($this->fields) > 0) {
            $query .= ' (';
            foreach ($this->fields as $field) {
                $query .= $this->connection->quote($field) . ',';
            }
            $query = substr($query, 0, -1);
            $query .= ')';
        }
        $query .= ' VALUES (';
        if (count($this->fields) > 0) {
            $queryVals = '';

            foreach ($this->fields as &$fields) {
                $queryVals .= '?,';
            }
            $query .= substr($queryVals, 0, -1) . ')';

            if ($this->onDupicateUpdate) {
                $queryVals = '';
                $query .= ' ON DUPLICATE KEY UPDATE ';
                foreach ($this->fields as &$field) {
                    $queryVals  .= $this->connection->quote($field) . ' = ?,';
                }
                if (!$this->dupicateParamsUsed) {
                    $setParams = $this->setParams;
                    foreach ($setParams as $setParam) {
                        $this->setParams []= $setParam;
                    }
                    $this->dupicateParamsUsed = true;
                }
                $query .= substr($queryVals, 0, -1);
            }
        } else {
            $query .= ')';
        }
        return $query;
    }
}
