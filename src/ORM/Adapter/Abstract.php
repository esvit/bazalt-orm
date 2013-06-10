<?php
/**
 * Abstract.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Adapter
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Adapter_Abstract
 *
 * @category   System
 * @package    ORM
 * @subpackage Adapter
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
abstract class ORM_Adapter_Abstract
{
    /**
     * Назва підключення
     */
    protected $name;

    /**
     * Сервер
     */
    protected $server = '';

    /**
     * Ім'я бази даних
     */
    protected $database = '';

    /**
     * Ім'я користувача
     */
    protected $username = '';

    /**
     * Пароль
     */
    protected $password = '';

    /**
     * Port
     */
    protected $port = '';

    /**
     * Опції підключення
     */
    protected $options = array();

    /**
     * Повертає строку підключення в форматі PDO
     *
     * @return string PDO Connection string
     */
    abstract function toPDOConnectionString();

    /**
     * Return PDO options
     *
     * @return array Options
     */
    abstract function getOptions();

    /**
     * Return queries for init connection encoding
     *
     * @return array Queries
     */
    abstract function getInitQueries();

    /**
     * Створює нове підключеня за назвою $name
     *
     * @param $name Назва підключення
     *
     * @return ORM_Connection_Abstract Об'єкт підключеня
     */
    abstract function connect($name);

    /**
     * Конструктор
     *
     * @param array $options Connection string options
     */
    public function __construct($options = array())
    {
        if (!is_array($options)) {
            throw new InvalidArgumentException('Connection string option must be array');
        }
        $this->options = $options;

        $this->server   = $this->getOption('server', 'localhost');
        $this->database = $this->getOption('database');
        $this->username = $this->getOption('username', 'root');
        $this->password = $this->getOption('password', '');
        $this->port     = $this->getOption('port', '3306');

        if (!$this->database) {
            throw new InvalidArgumentException('Unknown database');
        }
    }

    /**
     * Повертає ім`я підключення
     *
     * @return string Ім'я підключення
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Встановлює ім`я підключення
     *
     * @param string $name Name of this connection
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Повертає значення опції, або значення по замовчуванню
     *
     * @param string $name    Name of the option
     * @param string $default Default value of the option
     *
     * @return string Value of the option or default value if option don't exists
     */
    protected function getOption($name, $default = null)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }
        return $default;
    }

    /**
     * Return username
     *
     * @return string Username
     */
    public function getUser()
    {
        return $this->username;
    }

    /**
     * Return password
     *
     * @return string Password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Return database
     *
     * @return string Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Return server hostname
     *
     * @return string Hostname
     */
    public function getHostname()
    {
        return $this->server;
    }

    /**
     * Return port
     *
     * @return string Port
     */
    public function getPort()
    {
        return $this->port;
    }
}