<?php
/**
 * Manager.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Connection
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM\Connection;

use Bazalt\ORM as ORM;

/**
 * ORM_Connection_Manager
 * Клас управління підключеннями
 *
 * @category   System
 * @package    ORM
 * @subpackage Connection
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class Manager
{
    /**
     * Інстанс об'єкта ORM_Connection_Manager
     *
     * @var ORM_Connection_Manager
     */  
    protected static $instance = null;

    /**
     * Ім'я підключення по замовчуванню
     */
    const DEFAULT_CONNECTION_NAME = 'default';

    /**
     * Яке підключення використовувати за замовчуванням
     */
    protected $defaultConnectionName = self::DEFAULT_CONNECTION_NAME;

    /**
     * Масив підключень
     *
     * @var array
     */
    protected $connections = array();

    /**
     * getInstance
     *
     * @return ConnectionManager Singleton of object
     * @see Object, ISingleton
     */
    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
            //Config_Loader::init('database', self::$instance);
        }
        return self::$instance;
    }

    /**
     * Завантаження конфігурації
     *
     * @param mixed $config Об'єкт налаштувань
     *
     * @return void
     * @see Configuration, Config_IConfigurable
     */
    public function configure($config)
    {
        if (is_array($config['connections'])) {
            foreach ($config['connections'] as $connName => $connConfig) {
                $className = $connConfig->value;
                $attributes = $connConfig->attributes;
                self::add(new $className($attributes), $connName);
            }
        }

        if (isset($config['defaultConnection']) && !empty($config['defaultConnection'])) {
            $this->defaultConnectionName = $config['defaultConnection'];
        }

        if (!array_key_exists($this->defaultConnectionName, $this->connections)) {
        //    throw new Exception('Cannot find default connection "' . $this->defaultConnectionName . '"');
        }
    }

    /**
     * Встановлює назву з'єднання по замовчуванню
     *
     * @param string $name Назва з'єднання
     *
     * @return void
     */
    public static function setDefaultConnectionName($name)
    {
        self::getInstance()->defaultConnectionName = $name;
    }

    /**
     * Додає нове підключення до БД
     *
     * @param ORM_Adapter_Abstract $connString Строка підключення до БД
     * @param string               $name       Ім'я підключення
     *
     * @return DataBaseConnection Об'єкт підключення до БД
     */
    public static function add(ORM\Adapter\AbstractAdapter $connString, $name = null)
    {
        if (!$name) {
            $name = self::DEFAULT_CONNECTION_NAME;
        }
        $connMng = self::getInstance();
        if (array_key_exists($name, $connMng->connections)) {
            throw new \Exception('Connection with name ' . $name . ' allready exists');
        }
        $connMng->connections[$name] = $connString->connect($name);
        return $connMng->connections[$name];
    }

    /**
     * Повертає підключення до БД, якщо параметр $name не вказано, то повертає підключення за замовчуванням
     *
     * @param string $name Ім'я підключення
     *
     * @return DataBaseConnection Об'єкт підключення до БД
     */
    public static function getConnection($name = null)
    {
        $connMng = self::getInstance();
        if (!$name) {
            $name = $connMng->defaultConnectionName;
        }
        if (!array_key_exists($name, $connMng->connections)) {
            throw new \Exception('Cannot find connection with name "' . $name . '"');
        }
        return $connMng->connections[$name];
    }
}