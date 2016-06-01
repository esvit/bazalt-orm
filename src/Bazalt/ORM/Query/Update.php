<?php
/**
 * Update.php
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
 * ORM_Query_Update
 * Генерує UPDATE запит до БД
 *
 * @category   System
 * @package    ORM
 * @subpackage Query
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Update extends Builder
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
     * Масив ORDER BY параметрів
     *
     * @var array
     */
    protected $orderBy = array();
    
    /**
     * Флаг - видаляти автоматично кеш при апйдейті чи ні
     *
     * @var bool
     */
    protected $autoClearCache = true;

    /**
     * Повертає масив параметрів для запиту
     *
     * @return array
     */
    protected function getQueryParams()
    {
        return array_merge($this->setParams, $this->whereParams);
    }
    
    /**
     * Встановлює флаг $autoClearCache
     *
     * @param mixed    $autoClearCache Значення
     *
     * @return \Bazalt\ORM\Query\Update
     */
    public function autoClearCache($autoClearCache = null)
    {
        if($autoClearCache === null) {
            return $this->autoClearCache;
        }
        $this->autoClearCache = (bool)$autoClearCache;
        return $this;
    }

    /**
     * Встановлює ліміт для запиту
     *
     * @param integer $from  Початкове значення ліміту
     * @param integer $count Кількість записів в результаті
     *
     * @return Update
     */
    public function limit($from, $count = null)
    {
        if (!is_numeric($from) || (!is_null($count) && !is_numeric($count))) {
            throw new \InvalidArgumentException();
        }
        $this->limitFrom = $from;
        $this->limitCount = $count;
        return $this;
    }

    /**
     * Встановлює ORDER BY параметри до запиту
     *
     * @param string $fields Список полів для ORDER BY
     *
     * @return Update
     */
    public function orderBy($fields)
    {
        $this->orderBy = self::explode($fields);
        return $this;
    }

    /**
     * Генерує SQL для запиту
     *
     * @return string
     */
    public function buildSQL()
    {
        if($this->autoClearCache) {
            ORM::cache()->removeByTags($this->getCacheTags());
        }

        $query  = 'UPDATE ' . $this->getFrom();
        $query .= ' SET ';
        $queryVals = '';

        foreach ($this->fields as $field) {
            if (strpos($field, '=') === false) {
                $queryVals  .= $this->connection->quote($field) . ' = ?,';
            } else {
                $queryVals .= $field . ',';
            }
        }
        if (empty($queryVals)) {
            return null;
        }

        $query  .= substr($queryVals, 0, -1) . ' ';
        if (!empty($this->where)) {
            $query .= 'WHERE ' . $this->where . ' ';
        }

        return $query;
    }
}
