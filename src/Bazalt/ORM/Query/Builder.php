<?php
/**
 * Builder.php
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
 * ORM_Query_Builder
 *
 * @category   System
 * @package    ORM
 * @subpackage Query
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
abstract class Builder extends ORM\Query
{
    /**
     * Назва моделі, в яку будуть завантажені результати вибірки 
     *
     * @var string
     */
    protected $fetchType = null;

    /**
     * Значення для полів $fields
     *
     * @var array
     */
    protected $setParams = array();

    /**
     * Назва таблиці для якої будується запит
     *
     * @var string
     */
    protected $from = array();
    
    /**
     * Список моделей, які є в запиті
     *
     * @var array
     */
    protected $models = array();

    /**
     * Умова вибірки
     *
     * @var string
     */
    protected $where;

    /**
     * Параметри умови
     *
     * @var array
     */
    protected $whereParams = array();

    /**
     * Масив аліасів вибірки
     *
     * @var array
     */
    protected $aliases = array();
    
    /**
     * Масив JOIN-ів, ORM_Query_Join
     *
     * @var array
     */
    protected $joins = array();

    /**
     * Кількість групувань where
     */
    protected $whereGroups = 0;

    /**
     * Флаг вказує чи група where пуста
     */
    protected $whereGroupEmpty = true;

    /**
     * Генерує SQL для запиту
     *
     * @return string
     */
    abstract function buildSQL();

    /**
     * @todo on next refactoring replace this by getters
     */
    public function __get($name)
    {
        if ($name == 'From') {
            return $this->getFrom();
        }
        $name = lcfirst($name);
        if (isset($this->$name)) {
            return $this->$name;
        }
    }

    /**
     * Генерує унікальний аліас для таблиці
     *
     * @param string $tableName Назва таблиці
     *
     * @return string
     */
    protected function generateAlias($tableName)
    {
        $char = strtolower($tableName{0});
        $alias = $char;
        $num = 1;
        while (in_array($alias, $this->aliases)) {
            $alias = $char . ($num++);
        }
        return $alias;
    }
    
    /**
     * Додає LEFT JOIN до запиту
     *
     * @param string $name       Назва джойна
     * @param array  $conditions Масив умов
     *
     * @return ORM_Query_Builder
     */
    public function leftJoin($name, $conditions = array())
    {
        $this->joins[] = new Join($name, $conditions, Join::LEFT_JOIN);
        return $this;
    }

    /**
     * Додає INNER JOIN до запиту
     *
     * @param string $name       Назва джойна
     * @param array  $conditions Масив умов
     *
     * @return ORM_Query_Builder
     */
    public function innerJoin($name, $conditions = array())
    {
        $this->joins[] = new Join($name, $conditions, Join::INNER_JOIN);
        return $this;
    }

    /**
     * Додає RIGHT JOIN до запиту
     *
     * @param string $name       Назва джойна
     * @param array  $conditions Масив умов
     *
     * @return this
     */
    public function rightJoin($name, $conditions = array())
    {
        $this->joins[] = new Join($name, $conditions, Join::RIGHT_JOIN);
        return $this;
    }

    /**
     * Додає OUTER JOIN до запиту
     *
     * @param string $name       Назва джойна
     * @param array  $conditions Масив умов
     *
     * @return this
     */
    public function outerJoin($name, $conditions = array())
    {
        $this->joins[] = new Join($name, $conditions, Join::OUTER_JOIN);
        return $this;
    }

    /**
     * Повертає масив параметрів для запиту
     *
     * @return array
     */
    protected function getQueryParams()
    {
        return $this->whereParams;
    }

    /**
     * Встановлює модель або моделі (розділені комою), для якої буде будуватись запит
     *
     * @param string $name Назва таблиці
     *
     * @throws Exception
     * @return ORM_Query_Builder
     */
    public function from($name)
    {
        if (is_string($name)) {
            $from = self::explode($name);
        } else if (!is_array($name)) {
            $from = array($name);
        }
        // $this->from = array();
        $needAlias = count($from) > 1;
        if (count($from) == 1 && !is_object($from[0])) {
            $names = explode(' ', trim($from[0]));
            $this->fetchType = $names[0];
        }

        foreach ($from as $class) {
            if (is_string($class)) {
                $names = explode(' ', trim($class));

                $alias = null;
                $this->models []= $names[0];
                try {
                    $tableName = ORM\BaseRecord::getTableName($names[0]);
                    $connection = ORM\BaseRecord::getSQLConnectionNameByModel($names[0]);
                    $this->connection(ORM\Connection\Manager::getConnection($connection));

                    if (empty($tableName)) {
                        throw new \Exception('Invalid model "' . $names[0] . '"');
                    }
                    if (count($names) == 1) {
                        $alias = $needAlias ? $this->generateAlias($tableName) : null;
                    } else if (count($names) == 2) {
                        $alias = $names[1];
                    } else {
                        $this->from[] = $class;
                        continue;
                        //throw new InvalidArgumentException();
                    }
                } catch (ORM\Exception\Table $ex) {
                    $tableName = $name;
                    $this->fetchType = 'stdClass';
                    $this->connection(ORM\Connection\Manager::getConnection(ORM\Connection\Manager::DEFAULT_CONNECTION_NAME));
                }

                if (is_null($alias)) {
                    $this->from[] = $tableName;
                } else {
                    $this->aliases[] = $alias;
                    $this->from[$alias] = $tableName;
                }
            } else if ($class instanceof Bazalt\ORM\Builder) {
                $sql = $class->buildSQL();
                $alias = $this->generateAlias($sql);

                $this->whereParams = array_merge($class->WhereParams, $this->whereParams);
                $this->aliases[] = $alias;
                $this->from[$alias] = '(' . $class->buildSQL() . ')';
            }
        }
        return $this;
    }

    /**
     * Повертає масив тегів кешу для запиту
     *
     * @return array Масив тегів
     */
    protected function getCacheTags()
    {
        return $this->from;
    }

    /**
     * Повертає список таблиць і аліасів для запиту
     *
     * @return string 
     */
    protected function getFrom()
    {
        $str = '';
        foreach ($this->from as $k => $i) {
            if (!empty($i)) {
                $str .= $i;
                if (!is_numeric($k)) {
                    $str .= ' AS ' . $k;
                }
                $str .= ', ';
            }
        }
        return substr($str, 0, -2);
    }

    /**
     * Встановлює об'єкт моделі (тобто всі її поля) або пару 'назва стовпця' => 'значення'
     * для INSERT або UPDATE запиту
     *
     * @param ORM_Record|string $o     Об'єкт моделі або назва поля
     * @param mixed            $param Значення
     *
     * @return ORM_Query_Builder 
     */
    public function set($o, $param = null)
    {
        //check if $o is ORM_Record object
        if ($o instanceof ORM\Record && count($o->getColumns()) > 0) {
            $obj = $o;
            foreach ($obj->getColumns() as $column) {
                $fieldName = $column->name();
                if (($column->isPrimaryKey() || $column->isAutoIncrement()) && $this instanceof Update) {
                    $this->andWhere($fieldName . ' = ?', $obj->getField($fieldName));
                } else if (array_key_exists($fieldName, $obj->getSettedFields())) {
                    $this->_set($fieldName, $obj->getField($fieldName));
                }
            }
        } else if (strpos($o, '=') === false) {
            $field = $o;
            if (is_null($param)) {
                //throw new InvalidArgumentException();
                $param = null;
            }
            $this->_set($field, $param);
        } else {
            $this->fields[] = $o;
        }
        return $this;
    }

    /**
     * Встановлює пару 'назва стовпця' => 'значення' для INSERT або UPDATE запиту
     *
     * @param string $field Назва поля
     * @param mixed  $param Значення
     *
     * @return void 
     */
    private function _set($field, $param)
    {
        $this->fields []= $field;
        $this->setParams[]= $param;
    }    

    /**
     * Аналог натівної ф-ції explode
     *
     * @param string $string Вхідна строка
     * @param string $sep    Сепаратор
     *
     * @return array 
     */
    protected function explode($string, $sep = ',')
    {
        $arr = explode($sep, $string);
        foreach ($arr as &$field) {
            $field = trim($field);
        }
        return $arr;
    }

    /**
     * Додає до запиту WHERE "вираз"
     *
     * @param string $condition Вираз
     * @param array|string $params Параметри виразу
     *
     * @return ORM_Query_Builder
     */
    public function where($condition, $params = array())
    {
        $this->where = $condition;
        $this->whereGroupEmpty = false;
        if (is_array($params)) {
            $this->whereParams = $params;
        } else {
            $this->whereParams = array($params);
        }
        return $this;
    }

    /**
     * Додає до WHERE "вираз"
     *
     * @param string $condition Вираз
     * @param string $params    Параметри виразу
     *
     * @return ORM_Query_Builder 
     */
    protected function addWhere($condition, $params = array())
    {
        $conditions = explode(' ', $condition);
        $conditions[0] = $conditions[0];

        $this->where .= ' (' . implode(' ', $conditions) . ')';
        if (is_array($params)) {
            $this->whereParams = array_merge($this->whereParams, $params);
        } else {
            $this->whereParams []= $params;
        }
        return $this;
    }

    /**
     * Додає до WHERE AND "вираз"
     *
     * @param string $condition Вираз
     * @param array|string $params Параметри виразу
     *
     * @return ORM_Query_Builder|ORM_Query_Select
     */
    public function andWhere($condition, $params = array())
    {
        if ($this->whereGroupEmpty) {
            $this->whereGroupEmpty = false;
        } else {
            $this->where .= ' AND';
        }
        $this->addWhere($condition, $params);
        return $this;
    }

    /**
     * Додає до WHERE OR "вираз"
     *
     * @param string $condition Вираз
     * @param array|string $params Параметри виразу
     *
     * @return ORM_Query_Builder
     */
    public function orWhere($condition, $params = array())
    {
        if ($this->whereGroupEmpty) {
            $this->whereGroupEmpty = false;
        } else {
            $this->where .= ' OR';
        }
        $this->addWhere($condition, $params);
        return $this;
    }

    /**
     * Додає до WHERE гурпу умов ( ... ) через AND
     *
     * @return ORM_Query_Builder 
     */
    public function andWhereGroup()
    {
        $this->whereGroupEmpty = true;
        $this->whereGroups ++;
        if (!empty($this->where)) {
            $this->where .= ' AND ';
        }
        $this->where .= ' (';
        return $this;
    }

    /**
     * Додає до WHERE гурпу умов ( ... ) через OR
     *
     * @return ORM_Query_Builder 
     */
    public function orWhereGroup()
    {
        $this->whereGroupEmpty = true;
        $this->whereGroups ++;
        if (!empty($this->where)) {
            $this->where .= ' OR ';
        }
        $this->where .= ' (';
        return $this;
    }


    /**
     * Закриває відкриту раніше групу умов, доданих через andWhereGroup або orWhereGroup
     *
     * @return ORM_Query_Builder 
     */
    public function endWhereGroup()
    {
        /*if ($this->where{strlen($this->where) - 1} == '(') {
            $this->where .= '1';
        }*/
        $this->whereGroups--;
        $this->where .= ')';
        return $this;
    }

    /**
     * Додає до WHERE IN ("вираз")
     *
     * @param string                  $field Назва поля
     * @param ORM_Query_Builder|array $items Запит ORMQuery або масив значень
     * @param string                  $oper  Оператор через який буде додано вираз (AND чи OR)
     * @param bool                    $not   Флаг, якщо встановлено true, то додає NOT перед виразом, по замовчуванню false
     *
     * @throws Exception
     * @return ORM_Query_Builder
     */
    protected function addWhereIn($field, $items, $oper = 'AND', $not = false)
    {
        if ($items instanceof \Bazalt\ORM\Query\Builder) {
            $this->whereParams = array_merge($this->whereParams, $items->whereParams);
            $items = $items->buildSQL();
        } else if (is_array($items)) {
            if (count($items) == 0) {
                return $this;
            }
            $items = '"'.implode('","', $items).'"';
        }  else {
            throw new \Exception('Invalid argument for function whereIn');
        }
        if (!empty($this->where)) {
            $this->where .= ' ' . $oper;
        }
        $this->whereGroupEmpty = false;
        $this->where .= ' ' . $field . ($not ? ' NOT' : '') . ' IN (' . $items . ')';
        return $this;
    }

    /**
     * Додає до WHERE AND IN ("вираз")
     *
     * @param string                $field Назва поля
     * @param ORM_Query_Builder|array $items Запит ORMQuery або масив значень
     *
     * @return ORM_Query_Builder 
     */
    public function whereIn($field, $items)
    {
        return $this->addWhereIn($field, $items);
    }

    /**
     * Додає до WHERE AND IN ("вираз")
     *
     * @param string                $field Назва поля
     * @param ORM_Query_Builder|array $items Запит ORMQuery або масив значень
     *
     * @return ORM_Query_Builder 
     */
    public function andWhereIn($field, $items)
    {
        return $this->addWhereIn($field, $items);
    }

    /**
     * Додає до WHERE OR IN ("вираз")
     *
     * @param string                $field Назва поля
     * @param ORM_Query_Builder|array $items Запит ORMQuery або масив значень
     *
     * @return ORM_Query_Builder 
     */
    public function orWhereIn($field, $items)
    {
        return $this->addWhereIn($field, $items, 'OR');
    }

    /**
     * Додає до WHERE AND NOT IN ("вираз")
     *
     * @param string                $field Назва поля
     * @param ORM_Query_Builder|array $items Запит ORMQuery або масив значень
     *
     * @return ORM_Query_Builder 
     */
    public function notWhereIn($field, $items)
    {
        return $this->addWhereIn($field, $items, 'AND', true);
    }

    /**
     * Додає до WHERE AND NOT IN ("вираз")
     *
     * @param string                $field Назва поля
     * @param ORM_Query_Builder|array $items Запит ORMQuery або масив значень
     *
     * @return ORM_Query_Builder 
     */
    public function andNotWhereIn($field, $items)
    {
        return $this->addWhereIn($field, $items, 'AND', true);
    }

    /**
     * Додає до WHERE OR NOT IN ("вираз")
     *
     * @param string                $field Назва поля
     * @param ORM_Query_Builder|array $items Запит ORMQuery або масив значень
     *
     * @return ORM_Query_Builder 
     */
    public function orNotWhereIn($field, $items)
    {
        return $this->addWhereIn($field, $items, 'OR', true);
    }

    /**
     * Повертає ключ в кеші для даного запиту
     *
     * @return string 
     */
    public function getCacheKey()
    {
        if ($this->connection == null) {
            $this->connection = ORM\Connection\Manager::getConnection();
        }
        return $this->connection->computeCacheKey($this->buildSQL(), $this->getQueryParams());
    }

    /**
     * Виконує запит та повертає обєкт PDO
     *
     * @return PDO 
     */
    protected function execute()
    {
        $this->query = $this->buildSQL();
        $this->params = $this->getQueryParams();
        if ($this->query == null) {
            return null;
        }
        return parent::execute();
    }

    /**
     * Генерує SQL запит з підставленими параметрами
     *
     * @return string SQL запит
     */
    public function toSQL()
    {
        $query = $this->buildSQL();
        $params = $this->getQueryParams();

        return self::getFullQuery($query, $params);
    }
}