<?php
/**
 * Mssql.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Adapter
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM\Adapter;

/**
 * Підключення для провайдера mssql
 *
 * @category   System
 * @package    ORM
 * @subpackage Adapter
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class Mssql extends AbstractAdapter
{
    /**
     * Провайдер
     */
    protected $provider = 'sqlsrv';

    /**
     * Порт
     */
    protected $port = '1433';

    /**
     * Конструктор
     *
     * @param array $options Connection string options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->port     = $this->getOption('port', 1433);

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
        //return 'odbc:Driver={SQL Server};Server=127.0.0.1,1437;Database=upgradecapital;uid=sa;pwd=uc5%44_0';
        return $this->provider . ':' . 
               'server=' . $this->server . ',' . $this->port . ';' .
               'database=' . $this->database;
    }

    /**
     * Return PDO options
     *
     * @return array Options
     */
    public function getOptions()
    {
        return array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
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
            'SET collation_connection = "utf8_unicode_ci"'
        );
    }

    /**
     * Створює нове підключеня за назвою $name
     *
     * @param $name Назва підключення
     *
     * @return ORM_Connection_Abstract Об'єкт підключеня
     */
    public function connect($name)
    {
        $this->name = $name;
        return new \Bazalt\ORM\Connection\Mssql($this);
    }
}