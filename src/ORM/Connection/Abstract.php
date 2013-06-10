<?php
/**
 * Abstract.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Connection
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

use Framework\Core\Logger;
use Bazalt\ORM as ORM;

/**
 * ORM_Connection_Abstract
 *
 * @category   System
 * @package    ORM
 * @subpackage Connection
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
abstract class ORM_Connection_Abstract
{
    /**
     * Connection adapter
     *
     * @see ORM_Adapter_Abstract
     */
    protected $connectionAdapter;

    /**
     * Count of last affected rows
     */
    protected $lastAffectedRows = null;

    /**
     * Last executed query
     */
    protected $lastQuery = null;

    /**
     * Count of executed queries
     */
    protected $queryCount = 0;

    /**
     * PDO object
     *
     * @see PDO
     */
    private $_PDOObject = null;

    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $logger = null;

    /**
     * Constructor
     *
     * @param ORM_Adapter_Abstract $adapter Connection adapter
     *
     * @see ORM_Adapter_Abstract
     */
    public function __construct(ORM_Adapter_Abstract $adapter)
    {
        $this->connectionAdapter = $adapter;
        $this->logger = new Logger(get_class($this));
    }

    /**
     * Return PDO object
     *
     * @return PDO object
     */
    private function _getPDO()
    {
        if ($this->_PDOObject == null) {
            $this->_PDOObject = new PDO(
                $this->connectionAdapter->toPDOConnectionString(), 
                $this->connectionAdapter->getUser(),
                $this->connectionAdapter->getPassword(),
                $this->connectionAdapter->getOptions()
            );

            $queries = $this->connectionAdapter->getInitQueries();
            if ($queries != null && @count($queries) > 0) {
                foreach ($queries as $query) {
                    $this->_PDOObject->query($query);
                }
            }
            $this->_PDOObject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->_PDOObject;
    }

    /**
     * Розпочинає транзакцію
     *
     * @return void
     */
    public function begin()
    {
        $this->_getPDO()->beginTransaction();
    }

    /**
     * Комітить транзакцію
     *
     * @return void
     */
    public function commit()
    {
        $this->_getPDO()->commit();
    }

    /**
     * Робить відкат змін в межах розпочатої транзакції
     *
     * @return void
     */
    public function rollBack()
    {
        $this->_getPDO()->rollBack();
    }

    /**
     * Bind params to PDO
     *
     * @param PDOStatement $st     PDO Statement
     * @param array        $params Params of query
     *
     * @return void
     */
    protected function bindParams(PDOStatement $st, $params = array())
    {
        if (is_array($params)) {
            $num = 1;
            foreach ($params as $value) {
                if (is_int($value)) {
                    $param = PDO::PARAM_INT;
                    $sParam = 'int';
                } elseif(is_bool($value)) {
                    $param = PDO::PARAM_BOOL;
                    $sParam = 'bool';
                } elseif(is_null($value)) {
                    $param = PDO::PARAM_NULL;
                    $sParam = 'null';
                } elseif(is_string($value) || is_float($value)) {
                    $param = PDO::PARAM_STR;
                    $sParam = 'string';
                } else {
                    $param = FALSE;
                    $sParam = 'default';
                }

                //$this->logger->info(sprintf('Bind param #%d = "%s" AS %s', $num, $value, $sParam));

                $st->bindValue($num++, $value, $param);
            }
        }
    }

    /**
     * Execute query on database and return count of affected rows
     *
     * @param string $query Query
     *
     * @return int Count of affected rows
     */
    public function exec($query)
    {
        $this->lastQuery = $query;
        Logger::getInstance()->info($query, __CLASS__);
        $this->lastAffectedRows = $this->_getPDO()->exec($query);
        $this->queryCount++;
        return $this->lastAffectedRows;
    }

    /**
     * Execute query width params on database
     *
     * @param string $query  Query
     * @param array  $params Params of query
     *
     * @throws ORM_Exception_Query
     * @return PDOStatement Result of query
     */
    public function query($query, $params = array())
    {
        $this->lastQuery = $query;
        try {
            //$profile = Logger::start(__CLASS__, $query);
            $res = $this->_getPDO()->prepare($query);
            if (count($params) > 0) {
                $this->bindParams($res, $params);
            }
            if (STAGE == DEVELOPMENT_STAGE) {
                $this->logger->info(ORM\Query::getFullQuery($query, $params));
            }
            $res->execute();
            //Logger::stop($profile);
        } catch (PDOException $ex) {
            throw new ORM_Exception_Query($ex, $query, $params);
        }
        $this->queryCount++;
        return $res;
    }

    /**
     * Calculate cache key for query with params
     *
     * @param string $query  Query
     * @param array  $params Params of query
     *
     * @return string Cache key
     */
    public function computeCacheKey($query, $params = array())
    {
        $cacheKey = $query . '<' . implode('O_o', $params);
        return $cacheKey;
    }

    /**
     * Return last inserted id
     *
     * @return mixed Last inserted id
     */
    public function getLastInsertId()
    {
        return $this->_getPDO()->lastInsertId();
    }

    /**
     * Повертає інформацію про помилку, яка виникла під чкас виконання запиту
     * @link http://php.net/manual/en/pdo.errorinfo.php
     *
     * @return array Інформація про помилку
     */
    public function getErrorInfo()
    {
        return $this->_getPDO()->errorInfo();
    }

    /**
     * Return connection adapter
     *
     * @return ORM_Adapter_Abstract Adapter
     */
    public function getConnectionAdapter()
    {
        return $this->connectionAdapter;
    }
}