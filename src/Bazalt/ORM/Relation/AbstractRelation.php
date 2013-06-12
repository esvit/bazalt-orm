<?php

namespace Bazalt\ORM\Relation;

use Bazalt\ORM\Record;

abstract class AbstractRelation
{
    protected $dispatcher;

    /**
     * Назва моделі до якої іде звязок
     *
     * @var string
     */    
    protected $name;

    /**
     * Назва поля (стовпця) моделі від якої йде звязок.      
     *
     * @var string
     */    
    protected $column;
    
    /**
     * Назва проміжної моделі
     *
     * @var string
     */    
    protected $refTable;
    
    /**
     * Назва поля (стовпця) моделі до якої йде звязок.     
     *
     * @var string
     */    
    protected $refColumn;
    
    /**
     * Масив додаткових параметрів, які будуть враховуватись при вибірках по звязку
     *
     * @var mixed
     */   
    protected $additionalParams = null;

    /**
     * Поточний об'єкт, з яким відбувається робота 
     *
     * @var Record
     */
    protected $baseObject = null;

    /**
     * Внутрішній вказівник для Iterator
     *
     * @var integer
     */   
    private $_position = 0;

    /**
     * Constructor
     *
     * @param string $name             Назва моделі до якої іде звязок
     * @param string $column           Назва поля (стовпця) моделі від якої йде звязок
     * @param string $refTable         Назва проміжної моделі
     * @param string $refColumn        Назва поля (стовпця) моделі до якої йде звязок     
     * @param string $additionalParams Масив додаткових параметрів, 
     *                                 які будуть враховуватись при вибірках по звязку
     */
    public function __construct($name, $column, $refTable, $refColumn, $additionalParams = null)
    {
        $this->name = $name;
        $this->column = $column;
        $this->refTable = $refTable;
        $this->refColumn = $refColumn;
        $this->additionalParams = $additionalParams;
    }

    /**
     * Генерує запит для вибірки звязаних обєктів
     *
     * @return SelectQueryBuilder
     */
    abstract public function getQuery();

    /**
     * Генерує Sql скрипт для звязку @deprecated
     *
     * @param Record $model Модель до якої йде звязок
     * 
     * @return string
     */
    abstract public function generateSql($model);

    /**
     * Встановлює поточний об'єкт
     *
     * @param Record &$object поточний об'єкт
     *
     * @return void
     */
    public function baseObject(Record &$object = null)
    {
        if ($object !== null) {
            $this->baseObject = $object;
            return $this;
        }
        return $this->baseObject;
    }

    /**
     * Перевіряє чи відповідає тип об'єкта з яким працюють методи 
     * add,remove,has типу який задано в зв'язку
     *
     * @param Record $item Об'єкт, який потрібно перевірити
     *
     * @exception InvalidArgumentException
     *
     * @return void
     */
    protected function checkType(Record $item)
    {
        if (!$item instanceof $this->name) {
            throw new InvalidArgumentException('Invalid type of argument "' . get_class($item) . '",'.
                'must be subclass of "' . $this->name . '"');
        }
    }

    /**
     * Визначає чи буде повертати обєкт звязку 
     * як результат звернення один обєкт чи колекцію
     *
     * @return bool
     */ 
    public function isManyResult()
    {
        return $this instanceof IRelationMany;
    }

    /**
     * Повертає масив всіх звязаних з поточним обєктом записів з БД
     *
     * @return bool
     */ 
    public function getAll()
    {
        return $this->get();
    }

    /**
     * Викликається після створення зв'язку для ініціалізації моделі
     *
     * @param Record $model Об'єкт моделі
     *
     * @return void
     */
    public function initForModel($model)
    {
    }

    /**
     * Додає додаткові пармаетри звязку з $this->additionalParams до вибірки
     *
     * @param ORM_Query $q Запит вибірки
     *
     * @return void
     */
    protected function applyAddParams(\Bazalt\ORM\Query $q)
    {
        if ($this->additionalParams) {
            foreach ($this->additionalParams as $name => $value) {
                if (strpos($name, '.') === false) {
                    $name = 'ft.' . $name;
                }
                if ($value === null) {
                    $q->andWhere($name . ' IS ?', null);
                } else {
                    $q->andWhere($name . ' = ?', $value);
                }
            }
        }    
    }

    /** 
     * Повертає к-сть записів
     * 
     * @return integer
     */
    public function count()
    {
        return count($this->getAll());
    }    

    /** 
     * Встановлює внутрішній вказівник на 0
     * 
     * @return void
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /** 
     * Повертає поточний обєкт зі списку
     * 
     * @return mixed
     */
    public function current()
    {
        $arr = $this->getAll();
        return $arr[$this->_position];
    }

    /** 
     * Повертає поточне значення внутрішнього вказівника
     * 
     * @return integer
     */
    public function key()
    {
        return $this->_position;
    }

    /** 
     * Встановлює внутрішній вказівник на наступне значення
     * 
     * @return void
     */
    public function next()
    {
        $this->_position++;
    }

    /** 
     * Перевіряє чи існує обєкт в масиві
     * 
     * @return bool
     */
    public function valid()
    {
        $arr = $this->getAll();
        return isset($arr[$this->_position]);
    }

    public function dispatcher()
    {
        if (!$this->dispatcher) {
            $this->dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        }
        return $this->dispatcher;
    }
}
