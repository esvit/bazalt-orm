<?php

namespace Bazalt\ORM;

abstract class BaseRecord implements \IteratorAggregate
{
    /**
     * Array of extensions
     *
     * @var array
     */
    protected static $extensions = array();

    /**
     * Обробляти при виклику __get
     */
    const ON_FIELD_GET = 1;

    /**
     * Обробляти при виклику __set
     */
    const ON_FIELD_SET = 2;

    /**
     * Обробляти при збереженні
     */
    const ON_RECORD_SAVE = 4;

    /**
     * Обробляти, якщо поле встановлене
     */
    const FIELD_IS_SETTED = 4;

    /**
     * Обробляти, якщо поле не встановлене
     */
    const FIELD_NOT_SETTED = 8;

    /**
     * Ініціалізує поля
     *
     * @return void
     */
    abstract protected function initFields();

    /**
     * Ініціалізує звязки
     *
     * @return void
     */
    abstract function initRelations();

    /**
     * Ініціалізує плагіни
     *
     * @codeCoverageIgnore
     * @return void
     */
    public function initPlugins()
    {
    }
    
    /**
     * Ініціалізує плагіни
     *
     * @codeCoverageIgnore
     * @return void
     */
    protected function initIndexes()
    {
    }

    /**
     * Масив усіх таблиць
     *
     * @var string
     */
    protected static $allTables = array();

    /**
     * Масив усіх ключів
     *
     * @var string
     */
    protected static $allKeys = array();

    /**
     * Масив усіх звязків
     *
     * @var string
     */
    protected static $allReferences = array();

    /**
     * Масив усіх моделей
     *
     * @var string
     */
    protected static $allModels = array();

    /**
     * Назва таблиці в СУБД, яка буде відповідати цій моделі
     *
     * @var string
     */ 
    protected $tableName;

    /**
     * Назва моделі
     *
     * @var string
     */
    protected $modelName;

    /**
     * Table engine (InnoDB or MyISAM)
     *
     * @var string
     */ 
    protected $engine;
 
    /**
     * Масив усіх індексів
     *
     * @var string
     */
    protected static $indexes = array();

    /**
     * Масив усіх плагінів
     *
     * @var string
     */
    protected static $plugins = array();

    /**
     * Масив усіх з'єднань з серврером
     *
     * @var string
     */
    protected static $connections = array();

    /**
     * Значення, які були змінені при редаруванні
     *
     * @var array
     */     
    protected $values = array();
    
    /**
     * Значення, які були завантажені з БД
     *
     * @var array
     */     
    protected $oldValues = array();
    
    /**
     * Флаг о заполнении поля
     *
     * @var array
     */     
    protected $setted = array();

    /**
     * Флаг о изменении поля
     *
     * @var array
     */     
    protected $updated = array();
    
    /**
     * Колонка з автоінкрементом
     *
     * @var ORMColumn
     */
    protected $autoIncrementColumn = null;

    /**
     * Масив евентів плагінів
     *
     * @var array
     */
    protected static $pluginsEvents = array();
    
    
    /**
     * Constructor
     *
     * @param string $name      Назва таблиці
     * @param string $modelName Назва моделі
     * @param string $engine    Engine (InnoDB, MyISAM)
     *
     * @throws ORM_Exception_Model
     */
    public function __construct($name, $modelName, $engine = null)
    {
        if (!$name) {
            throw new \ORM_Exception_Model('Table name cannot be empty', $this);
        }
        if (!$modelName) {
            throw new \ORM_Exception_Model('Model name cant be empty "' . $name . '"', $this);
        }
        $this->engine = $engine;

        $this->tableName = $name;

        $this->modelName = $modelName;

        $this->initModel($name, $modelName);
    }

    /**
     * Підписує на евент моделі
     *
     * @param string   $model     Назва моделі
     * @param int      $type      Тип евента (ON_FIELD_GET, ON_FIELD_SET, ON_RECORD_SAVE)
     * @param callable $callback  Callback ф-ція або метод
     * @param int      $condition Умова вкилкику калбека
     *
     * @return void
     */
    public static function registerEvent($model, $type = self::ON_FIELD_GET, $callback, $condition = null)
    {
        if ($condition === null) {
            $condition = self::FIELD_IS_SETTED | self::FIELD_NOT_SETTED;
        }
        if ($type & self::ON_FIELD_GET) {
            self::$pluginsEvents[$model][self::ON_FIELD_GET][] = array(
                'callback'  => $callback,
                'condition' => $condition
            );
        }
        if ($type & self::ON_FIELD_SET) {
            self::$pluginsEvents[$model][self::ON_FIELD_SET][] = array(
                'callback'  => $callback,
                'condition' => $condition
            );
        }
        if ($type & self::ON_RECORD_SAVE) {
            self::$pluginsEvents[$model][self::ON_RECORD_SAVE][] = array(
                'callback'  => $callback,
                'condition' => $condition
            );
        }
    }

    /**
     * Ініціалізує модель 
     *
     * @param string $name      Назва таблиці
     * @param string $modelName Назва моделі
     *
     * @return void
     */
    private function initModel($name, $modelName)
    {
        if (!array_key_exists($name, self::$allTables)) {
            self::$allTables[$name] = array();
            self::$allReferences[$name] = array();
            self::$allKeys[$name] = array();
            self::$allModels[$modelName] = $name;

            self::$connections[$modelName] = $this->getSQLConnectionName();

            //Init fields at first request
            $this->initFields();

            //Init relations at first request
            $this->initRelations();

            //Init indexes
            // $this->initIndexes();
            
            //Init plugins
            $this->initPlugins();
            $this->initModelPlugins();
        }
    }

    /**
     * __sleep
     *
     * @return array
     */
    public function __sleep()
    {
        return array('tableName', 'values', 'setted');
    }

    /**
     * __wakeup
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->initModel($this->tableName, get_class($this));
    }

    /**
     * Повертає назву конекта до БД по замовчуванню
     *
     * @return string
     */
    public function getSQLConnectionName()
    {
        return Connection\Manager::DEFAULT_CONNECTION_NAME;
    }

    /**
     * Повертає об'єкт конекта до БД для моделі
     *
     * @param string $modelName Назва моделі
     *
     * @return ORM_Connection_Abstract
     */
    public static function getSQLConnectionNameByModel($modelName)
    {
        if (!isset(self::$connections[$modelName])) {
            return null;
        }
        return self::$connections[$modelName];
    }

    /**
     * Повертає масив всіх установлених полів
     *
     * @return array
     */
    public function getSettedFields()
    {
        return $this->setted;
    }

    /**
     * Повертає масив всіх значень об'єкту моделі
     *
     * @return array
     */
    public function getFieldsValues()
    {
        return $this->values;
    }

    /**
     * Повертаэ значення поля об'єкту моделі або null якщо воно не встановлене
     *
     * @param string $field Назва поля
     *
     * @return mixed Значення
     */
    public function getField($field)
    {
        if (!array_key_exists($field, $this->values)) {
            return null;
        }
        return $this->values[$field];
    }

    /**
     * Встановлює значення в поле об'єкту моделі
     *
     * @param string $field Назва поля
     * @param string $value Значення
     * @param bool $isCreate
     *
     * @return void
     */
    public function setField($field, $value, $isCreate = false)
    {
        if ($isCreate) {
            $this->values[$field] = $value;
            $this->oldValues[$field] = $value;
        } elseif (!isset($this->oldValues[$field]) || $this->values[$field] !== $value) {
            if (isset($this->values[$field])) {
                $this->oldValues[$field] = $this->values[$field];
            }
            $this->values[$field] = $value;    
        }
        $this->setted[$field] = true;
    }

    /**
     * Повертає масив стовпців ORM_Column
     *
     * @throws Exception
     * @return array
     */
    public function &getColumns()
    {
        if (!array_key_exists($this->tableName, self::$allTables)) {
            //return array();
            throw new \Exception('Table "' . $this->tableName . '" not found');
        }
        return self::$allTables[$this->tableName];
    }

    /**
     * Повертає масив стовпців звязків ORM_Relation
     *
     * @throws Exception
     * @return array
     */
    public function &getReferences()
    {
        if (!array_key_exists($this->tableName, self::$allReferences)) {
            //return array();
            throw new \Exception('Table "' . $this->tableName . '" not found ');
        }
        return self::$allReferences[$this->tableName];
    }

    /**
     * Повертає масив усіх звязків
     *
     * @return array
     */  
    public static function getAllReferences()
    {
        return self::$allReferences;
    }
    
    /**
     * Повертає масив моделей які мають плагін $plugin
     *
     * @param string $plugin Назва плагіна
     *
     * @return array
     */  
    public static function getByPlugin($plugin)
    {
        $models = array();
        foreach (array_keys(self::$plugins) as $modelName) {
            if (array_key_exists($plugin, self::$plugins[$modelName])) {
                $models []= $modelName;
            }
        }
        return $models;
    }
    
    /**
     * Повертає масив плагінів моделі
     *
     * @return array
     */
    public function getPlugins()
    {
        $modelName = get_class($this);
        if (!isset(self::$plugins[$modelName])) {
            return null;
        }
        return self::$plugins[$modelName];
    }

    /**
     * Повертає плагін моделі
     *
     * @param $name
     * @return array
     */
    public function getPlugin($name)
    {
        $modelName = get_class($this);
        if (!isset(self::$plugins[$modelName]) && !isset(self::$plugins[$modelName][$name])) {
            return null;
        }
        return self::$plugins[$modelName][$name];
    }

    /**
     * Повертає масив індексів моделі
     *
     * @return array
     */
    public function getIndexes()
    {
        if (!isset(self::$indexes[$this->tableName])) {
            return null;
        }
        return self::$indexes[$this->tableName];
    }

    /**
     * Повертає об'єкт моделі
     *
     * @param string $className = null Назва моделі
     *
     * @return ORM_Record
     */
    public static function getTable($className)
    {
        if (!class_exists($className)) {
            return null;
        }
        return new $className();
    }

    /**
     * Повертає назву таблиці моделі
     *
     * @param string $className = null Назва моделі
     *
     * @return string
     */
    public static function getTableName($className)
    {
        if (!isset(self::$allModels[$className])) {
            $table = self::getTable($className);
            if ($table == null) {
                throw new Exception\Table('Table not found', $className);
            }
        }
        return self::$allModels[$className];
    }

    /**
     * Повертає масив стовпців моделі - об'єктів ORMColumn {@link ORMColumn}
     *
     * @param string $tableName Назва таблиці моделі
     *
     * @return array
     */ 
    public static function getAllColumns($tableName)
    {
        if (!array_key_exists($tableName, self::$allTables)) {
            throw new Exception\Table('Table not found', $tableName);
        }
        return self::$allTables[$tableName];
    }

    /**
     * Повертає масив первичних ключів моделі - об'єктів ORMColumn {@link ORMColumn}
     *
     * @param string $tableName Назва таблиці моделі
     *
     * @return ORM_Column[]
     */ 
    public static function getPrimaryKeys($tableName)
    {
        $tableName = self::getTableName($tableName);
        return self::$allKeys[$tableName];
    }

    /**
     * Повертає об'єкт автоінкрементного стовпця моделі
     *
     * @param string $tableName Назва таблиці моделі
     *
     * @throws ORM_Exception_Table
     * @return ORM_Column
     */
    public static function getAutoIncrementColumn($tableName)
    {
        $table = self::getTable($tableName);
        if ($table == null) {
            throw new Exception\Table('Table not found', $tableName);
        }
        $columns = $table->getColumns();
        foreach ($columns as $column) {
            if ($column->isAutoIncrement()) {
                return $column;
            }
        }
    }

    /**
     * Повертає значення автоінкрементного стовпця моделі
     *
     * @throws ORM_Exception_Table
     * @return mixed
     */
    public function getAutoIncrementValue()
    {
        foreach ($this->getColumns() as $column) {
            if ($column->isAutoIncrement()) {
                return (int) $this->{$column->name()};
            }
        }
        throw new Exception\Table('Table does not have any autoincrement columns', $this->tableName);
    }
    
    /**
     * Встановлює $value в поле $name
     *
     * @param string $name  Назва поля
     * @param mixed  $value Значення
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $return = false;
        if (isset(self::$pluginsEvents[get_class($this)]) && isset(self::$pluginsEvents[get_class($this)][self::ON_FIELD_SET])) {
            foreach (self::$pluginsEvents[get_class($this)][self::ON_FIELD_SET] as $event) {
                $callback = $event['callback'];
                if (($event['condition'] & self::FIELD_IS_SETTED  &&  isset($this->setted[$name])) ||
                    ($event['condition'] & self::FIELD_NOT_SETTED && !isset($this->setted[$name]))) {

                    $params = array($this, $name, $value);
                    $params [] = &$return;
                    call_user_func_array($callback, $params);
                }
            }
        }
        //$this->OnSet($this, $name, $value, &$return);
        if ($return !== false) {
            return;
        }

        $references = $this->getReferences();
        if ($references != null && array_key_exists($name, $references)) {
            $relation = clone $references[$name];
            $relation->baseObject($this);
            
            $relation->set($value);
            return;
        }

        $this->setField($name, $value);
    }

    /**
     * Повертає значення поля $name
     *
     * @param string $name Назва поля
     *
     * @return mixed
     */
    public function __get($name)
    {
        $return = false;
        if (isset(self::$pluginsEvents[get_class($this)]) && isset(self::$pluginsEvents[get_class($this)][self::ON_FIELD_GET])) {
            foreach (self::$pluginsEvents[get_class($this)][self::ON_FIELD_GET] as $event) {
                $callback = $event['callback'];
                if (($event['condition'] & self::FIELD_IS_SETTED  &&  isset($this->setted[$name])) ||
                    ($event['condition'] & self::FIELD_NOT_SETTED && !isset($this->setted[$name]))) {

                    $params = array($this, $name);
                    $params [] = &$return;
                    call_user_func_array($callback, $params);
                }
            }
        }
        //$this->OnGet($this, $name, &$return);
        if ($return !== false) {
            return $return;
        }

        if (array_key_exists($name, $this->values) && (isset($this->setted[$name]) && $this->setted[$name])) {
            return $this->values[$name];
        } else if (array_key_exists($name, $references = $this->getReferences())) {
            $relation = clone $references[$name];
            $relation->baseObject($this);

            return ($relation->isManyResult()) ? $relation : $relation->get();
        } else if (array_key_exists($name, $columns = $this->getColumns())) {
            // Якщо значення поля не задано, то встановити по замовчуванню
            if (!array_key_exists($name, $this->values)) {
                $column = $columns[$name];
                if ($column->hasDefault()) {
                    $this->values[$name] = $column->getDefault();
                    $this->setted[$name] = 1;
                } else {
                    return null;
                }
            }
            return $this->values[$name];
        }
        return null;
    }

    /**
     * Додає стовпчик в загальну модель
     *
     * @param string $name    Ім'я стовпчика
     * @param string $options Опції
     *
     * @throws ORM_Exception_Table
     * @return void
     */
    public function hasColumn($name, $options = null)
    {
        $columns = &self::$allTables[$this->tableName];
        if (!array_key_exists($name, $columns)) {
            $column = new Column($name, $options);
            if ($column->isAutoIncrement()) {
                if ($this->autoIncrementColumn != null) {
                    throw new Exception\Table('Table cannot have two autoincrement columns', $this->tableName);
                }
                $this->autoIncrementColumn = &$column;
            }
            $columns[$name] = $column;
            if ($column->isPrimaryKey()) {
                self::$allKeys[$this->tableName][$name] = &$column;
            }
        } else {
            return false;
            //throw new ORM_Exception_Table('Column "' . $name . '" already present in this model', $this->tableName);
        }
        return true;
    }

    /**
     * Видаляє стовпчик з моделі
     *
     * @param string $name Ім'я стовпця
     *
     * @return void
     */
    public function removeColumn($name)
    {
        $columns = &self::$allTables[$this->tableName];

        if (array_key_exists($name, $columns)) {
            unset($columns[$name]);
        }
        if (array_key_exists($name, self::$allKeys[$this->tableName])) {
            unset(self::$allKeys[$this->tableName][$name]);
        }
    }

    /**
     * Додає звязок в модель
     *
     * @param string                $name     ім'я звязка
     * @param ORM_Relation_Abstract $relation звязок
     *
     * @return void
     */
    public function hasRelation($name, Relation\AbstractRelation $relation)
    {
        $references = &$this->getReferences();
        if (!array_key_exists($name, $references)) {
            $references[$name] = $relation;
            $relation->initForModel($this);
        } else {
            throw new Exception\Table('Relation "' . $name . '" already present in this model', $this->tableName);
        }
    }

    /**
     * Додає плагін в модель
     *
     * @param string $name    Ім'я плагіна
     * @param array  $options Масив опцій
     *
     * @return void
     */    
    public function hasPlugin($name, $options = array())
    {
        self::$plugins[get_class($this)][$name] = $options;
    }
    
    /**
     * Додає плагін в модель
     *
     * @param ORM_Index_Abstract $index
     *
     * @return void
     */    
    public function hasIndex(\ORM_Index_Abstract $index)
    {
        self::$indexes[$this->tableName][$index->name()] = $index;
    }

    /**
     * Ініціалізує плагіни моделі
     *
     * @return void
     */
    protected function initModelPlugins()
    {
        if (array_key_exists(get_class($this), self::$plugins)) {
            foreach (self::$plugins[get_class($this)] as $name => $options) {
                $plugin = Plugin\AbstractPlugin::getPlugin($name);
                $plugin->initForModel($this, $options);
            }
        }
    }

    /**
     * Create an iterator because private/protected vars can't be seen by json_encode
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $arrResult = array();
        foreach ($this->values as $k => $i) {
            $arrResult[$k] = $i;
        }
        return new \ArrayIterator($arrResult);
    }

    /**
     * Перевіряє чи існує поле $name в об'єкті і чи було воно встановлене через __set
     *
     * @param string $name Назва поля
     *
     * @return bool
     */
    public function __isset($name)
    {
        $return = false;
        if (isset(self::$pluginsEvents[get_class($this)]) && isset(self::$pluginsEvents[get_class($this)][self::ON_FIELD_GET])) {
            foreach (self::$pluginsEvents[get_class($this)][self::ON_FIELD_GET] as $event) {
                $callback = $event['callback'];
                if (($event['condition'] & self::FIELD_IS_SETTED  &&  isset($this->setted[$name])) ||
                    ($event['condition'] & self::FIELD_NOT_SETTED && !isset($this->setted[$name]))) {

                    $params = array($this, $name);
                    $params [] = &$return;
                    call_user_func_array($callback, $params);
                }
            }
        }
        if ($return !== false) {
            return true;
        }

        if (array_key_exists($name, $this->setted)) {
            return true;
        } else if (array_key_exists($name, $this->getReferences())) {
            return true;
        } else if (array_key_exists($name, $this->getColumns())) {
            return true;
        }
        return false;
    }
    
    /**
     * Перевіряє чи існує стовпець $name в моделі
     *
     * @param string $name Назва поля
     *
     * @return bool
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->getColumns());
    }
    
    /**
     * Видаляє поле $name з об'єкта
     *
     * @param string $name Назва поля
     *
     * @return void
     */
    public function __unset($name)
    {
        unset($this->values[$name]);
        unset($this->setted[$name]);
    }

    /**
     * Повертає об'єкт у вигляді масиву
     *
     * @return array
     */
    public function toArray()
    {
        $res = array();

        foreach ($this->getColumns() as $column) {
            $fieldName = $column->name();
            $res[$fieldName] = $this->$fieldName;
        }
        $plugins = $this->getPlugins();
        if ($plugins) {
            foreach ($plugins as $name => $options) {
                $plugin = Plugin\AbstractPlugin::getPlugin($name);

                $res = $plugin->toArray($this, $res, $options);
            }
        }
        return $res;
    }

    /**
     * Заповнює об'єкт з масиву
     * 
     * Если данные ещё не заполненны - обнуляемм и заполняем их.
     * Иначе делаем обновление по масиву данных
     *
     * @param array $data Масив даних виду array( 'назва стовпця' => 'значення' )
     *
     * @return void
     */     
    public function fromArray($data)
    {
        if (!is_array($data)){
            return;
        }
        $em = empty($this->values);
        if ($em){
            $this->setted = array();
            $this->values = array();
            foreach ($this->getColumns() as $columnName => $column) {
                $this->values[$columnName] = null;
                $this->oldValues[$columnName] = null;
            }
        }
        foreach ($data as $field => $value) {
            $this->setField($field, $value, $em);
        }
    }

    /**
     * Перехват не існуючих методів для використання механізму extend
     *
     * @param string $func Ім'я методу
     * @param array  $args Список аргументів
     *
     * @throws Exception
     * @return void
     */
    public function __call($func, $args = array())
    {
        $className = get_class($this);
        if (array_key_exists($className, self::$extensions)) {
            $exts = self::$extensions[$className];
            foreach ($exts as $ext) {
                $extObject = Type::getObjectInstance($ext);
                if ($extObject->getType()->hasMethod($func)) {
                    $args = array_merge(array($this), $args);
                    return call_user_func_array(array($extObject, $func), $args);
                }
            }
        }
        throw new \Exception('Cannot find function ' . $func);
    }


    /**
     * Розширює методи моделі, напр. через плагіни
     *
     * @param string $className          Клас, який розширяють
     * @param string $classExtensionName Клас, яким доповнюють
     *
     * @throws InvalidArgumentException
     * @throws Exception
     * @return void
     */
    public static function extend($className, $classExtensionName)
    {
        if (!\Framework\Core\Helper\String::isValid($classExtensionName)) {
            throw new InvalidArgumentException('Invalid argument');
        }
        if (!$className) {
            throw new Exception('Cannot detect extend class');
        }

        if (!array_key_exists($className, self::$extensions)) {
            self::$extensions[$className] = array();
        }
        if (!in_array($classExtensionName, self::$extensions[$className])) {
            self::$extensions[$className][] = $classExtensionName;
        }
    }


    /**
     * Повертає назву моделі
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }
}