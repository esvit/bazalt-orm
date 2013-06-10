<?php
/**
 * Select.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Query
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM\Query;

use Bazalt\ORM\Query\Fetchable;

/**
 * ORM_Query_Select
 *
 * @todo Не привязні аліаси до $select та $orderBy полів запиту
 *
 * @category   System
 * @package    ORM
 * @subpackage Query
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class Select extends Builder
{
    use Fetchable;

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
     * Масив GROUP BY параметрів
     *
     * @var array
     */
    protected $groupBy = array();

    /**
     * Масив HAVING параметрів
     *
     * @var array
     */
    protected $having = array();

    /**
     * Номер поточної сторінки
     *
     * @var int
     */
    protected $pageNum = null;

    /**
     * К-ть записів на сторінку
     *
     * @var int
     */
    protected $countOnPage = 10;

    /**
     * К-ть записів
     *
     * @var int
     */
    protected $totalCount = null;

    /**
     * Встановлює поля, які ввійдуть у результат вибірки
     *
     * @param array $fields Масив полів, які ввійдуть у результат вибірки
     *
     * @return ORM_Query_Select
     */
    public function select($fields)
    {
        $this->select = self::explode($fields);
        return $this;
    }

    /**
     * Повертає масив тегів кешу для запиту
     *
     * @return array Масив тегів
     */
    protected function getCacheTags()
    {
        $from = $this->from;
        if (count($this->joins) > 0) {
            foreach ($this->joins as $join) {
                $from[] = $join->getTable();
            }
        }
        return $from;
    }
    
    /**
     * Генерує SQL для запиту
     *
     * @return string
     */
    public function buildSQL()
    {
        if (!$this->connection) {
            $this->connection = ORM_Connection_Manager::getConnection(ORM_Connection_Manager::DEFAULT_CONNECTION_NAME);
        }
        return $this->connection->buildSelectSQL($this);//для кросплатформенної реалізації запиту
    }

    /**
     * Встановлює ліміт для запиту
     *
     * @param integer $from  Початкове значення ліміту
     * @param integer $count Кількість записів в результаті
     *
     * @throws InvalidArgumentException
     * @return ORM_Query_Select
     */
    public function limit($from, $count = null)
    {
        if (!\Framework\Core\Helper\Number::isValid($from) || (!is_null($count) && !\Framework\Core\Helper\Number::isValid($count))) {
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
     * @return ORM_Query_Select
     */
    public function orderBy($fields)
    {
        $this->orderBy = self::explode($fields);
        return $this;
    }

    /**
     * Додає ORDER BY параметри до запиту
     *
     * @param string $fields Список полів для ORDER BY
     *
     * @return ORM_Query_Select
     */
    public function addOrderBy($fields)
    {
        $this->orderBy = array_merge($this->orderBy, self::explode($fields));
        return $this;
    }

    /**
     * Додає GROUP BY параметри до запиту
     *
     * @param string $fields Список полів для GROUP BY
     *
     * @return ORM_Query_Select
     */
    public function groupBy($fields = null)
    {
        if ($fields === null) {
            return $this->groupBy;
        }
        if (!empty($fields)) {
            $this->groupBy = self::explode($fields);
        } else {
            $this->groupBy = array();
        }
        return $this;
    }

    /**
     * Встановлює HAVING для запиту
     *
     * @param string $having Вираз для HAVING
     *
     * @return ORM_Query_Select
     */
    public function having($having = null)
    {
        if ($having === null) {
            return $this->having;
        }
        $this->having = $having;
        return $this;
    }

    /**
     * Розбиває результати запиту на сторінки і вертає результати заданої сторінки
     *
     * @param int $pageNum     Номер сторінки
     * @param int $countOnPage Кількість запитів на сторінку
     *
     * @return ORM_Query_Select
     */
    public function page($pageNum = 1, $countOnPage = 10)
    {
        if ($this->pageNum < 1) {
            $this->pageNum = 1;
        }
        $this->pageNum = $pageNum;
        $this->countOnPage = $countOnPage;

        $this->limitFrom = ($this->pageNum - 1) * $countOnPage;
        $this->limitCount = $countOnPage;

        return $this;
    }

    /**
     * Повертає к-ть записів
     *
     * @return int К-ть записів
     */
    public function totalCount()
    {
        return $this->totalCount;
    }

    /**
     * Повертає к-ть сторінок
     *
     * @return int К-ть сторінок
     */
    public function pageCount()
    {
        $count = ceil($this->totalCount / $this->countOnPage);
        if ($count < 1) {
            return 1;
        }
        return $count;
    }
}