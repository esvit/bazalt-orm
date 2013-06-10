<?php
/**
 * Mysql.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Adapter
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM\Adapter;

use Framework\Core\Logger,
    Bazalt\ORM as ORM;

/**
 * Підключення для провайдера mysql
 *
 * @category   System
 * @package    ORM
 * @subpackage Adapter
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class Mysql extends AbstractAdapter
{
    /**
     * Провайдер
     */
    protected $provider = 'mysql';

    /**
     * Порт
     */
    protected $port = '3306';

    /**
     * Конструктор
     *
     * @param array $options Connection string options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->port     = $this->getOption('port', 3306);

        if (!is_numeric($this->port)) {
            throw new \InvalidArgumentException('Invalid port');
        }
        $this->port = intval($this->port);
    }

    /**
     * Повертає строку підключення в форматі PDO
     *
     * @return string PDO Connection string
     */
    public function toPDOConnectionString()
    {
        return $this->provider . ':' . 
               'host=' . $this->server . ';' .
               'port=' . $this->port . ';' .
               'dbname=' . $this->database;
    }

    /**
     * Return PDO options
     *
     * @return array Options
     */
    public function getOptions()
    {
        return array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
    }

    /**
     * Return queries for init connection encoding
     *
     * @return array Queries
     */
    public function getInitQueries()
    {
        return array(
            'SET CHARACTER SET UTF8',
            'SET character_set_client = "utf8"',
            'SET character_set_results = "utf8"',
            'SET collation_connection = "utf8_unicode_ci"',
            'SET time_zone = "UTC"'//write to DB in UTC 
        );
    }

    /**
     * Створює нове підключеня за назвою $name
     *
     * @param $name Назва підключення
     *
     * @return ORM\Connection\AbstractConnection Об'єкт підключеня
     */
    public function connect($name)
    {
        $this->name = $name;
        return new ORM\Connection\Mysql($this);
    }
}