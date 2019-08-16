<?php

namespace Bazalt\ORM;

use Bazalt\ORM;

class Query
{
    /**
     * Запит
     */
    protected $query = '';

    /**
     * Поточне підключення
     *
     * @var \Bazalt\ORM\Connection\AbstractConnection
     */
    protected $connection = null;

    /**
     * Параметри запиту
     */
    protected $params = array();

    /**
     * Теги кешу
     */
    protected $cacheTags = array();

    /**
     * Флаг вказує чи кешувати запит
     *
     * @since r1231
     */
    protected $cached = true;

    /**
     * Масив данних про помилку, якщо вона виникає під час виконання запиту
     */
    protected $error = null;

    /**
     * Construct
     * 
     * @param string $sql       SQL запит
     * @param array  $params    Параметри запиту
     * @param array  $cacheTags Теги кешу
     */
    public function __construct($sql = '', $params = array(), $cacheTags = array())
    {
        if ($params != null && !is_array($params)) {
            $params = array($params);
        }
        $this->query = $sql;
        $this->params = $params;
        $this->cacheTags = $cacheTags;
    }

    /**
     * Відключає кешування запиту
     *
     * @since r1231
     */
    public function noCache()
    {
        $this->cached = false;
        return $this;
    }

    /**
     * Повертає масив параметрів для запиту
     *
     * @return array
     */
    protected function getQueryParams()
    {
        return $this->params;
    }

    /**
     * Встановлює підключення до БД для запиту
     *
     * @param Connection\AbstractConnection $connection Підключення до БД
     *
     * @return Connection\AbstractConnection|Query
     */
    public function connection(Connection\AbstractConnection $connection = null)
    {
        if ($connection !== null) {
            $this->connection = $connection;
            return $this;
        }
        return $this->connection;
    }

    /**
     * Return last inserted id
     *
     * @return mixed Last inserted id
     */
    public function getLastInsertId()
    {
        return $this->connection->getLastInsertId();
    }

    /**
     * Повертає ключ в кеші для даного запиту
     *
     * @return string 
     */
    public function getCacheKey()
    {
        if ($this->connection == null) {
            $this->connection = Connection\Manager::getConnection();
        }
        return $this->connection->computeCacheKey($this->query, $this->getQueryParams());
    }

    /**
     * Виконує запит та повертає обєкт PDO
     *
     * @return \PDO
     */
    protected function execute()
    {
        //$profile = Logger::start(__CLASS__, __FUNCTION__);
        if ($this->connection == null) {
            $this->connection = Connection\Manager::getConnection();
        }
        try {
            $res = $this->connection->query($this->query, $this->getQueryParams());
        } catch(Bazalt\ORM\Exception\Deadlock $ex) {//let's try once more
            $res = $this->connection->query($this->query, $this->getQueryParams());
        }
        $this->error = $this->connection->getErrorInfo();
        //Logger::stop($profile);
        return $res;
    }

    /**
     * Виконує запит до БД
     * WARNING! Dont work with select, only on MySQL
     *
     * @param bool $returnCount Флаг, визначаэ повертати к-ть задіяних рядків чи ні
     *
     * @return int|void Кількість задіяних рядків
     */
    public function exec($returnCount = true)
    {
        $cacheKey = $this->getCacheKey();
//        $cached = ($this->cached) ? Cache::Singleton()->getCache($cacheKey) : false;
//
//        if ($cached !== false && defined('CACHE') && CACHE === true) {
//            return $cached;
//        }
        
        $res = $this->execute();
        if ($returnCount && $res) {
            $rowCount = $res->rowCount();
//            Cache::Singleton()->setCache($cacheKey, $rowCount, Cache::Singleton()->defaultLifeTime(), $this->getCacheTags());
            return $rowCount;
        }
    }

    /**
     * Повертає інформацію про помилку, яка виникла під чкас виконання запиту
     * @link http://php.net/manual/en/pdo.errorinfo.php
     *
     * @return array Інформація про помилку
     */
    public function getErrorInfo()
    {
        return $this->error;
    }

    /**
     * Генерує SQL запит з підставленими параметрами
     *
     * @return string SQL запит
     */
    public function toSQL()
    {
        return self::getFullQuery($this->query, $this->params);
    }

    /**
     * Формує повний SQL-запит з усіма заповненими параметрами
     *
     * @param string $query  Запит
     * @param array  $params Масив парамаетрів
     *
     * @return string SQL-запит
     */
    public static function getFullQuery($query, $params)
    {
        $keys = array();
        $values = array();
       
        # build a regular expression for each parameter
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $keys[]   = is_string($key) ? '/:' . $key . '/' : '/[?]/';

                $values[] = is_integer($value) ? intval($value) : '"' . addslashes($value) . '"';
            }
        }
       
        $query = preg_replace($keys, $values, $query, 1, $count);
        return $query;
    }

    /**
     * Повертає один результат вибірки
     *
     * @param string $baseClass Назва моделі
     *
     * @return mixed 
     */
    public function fetch($baseClass = 'stdClass')
    {
        $cacheKey = $this->getCacheKey();
        $cached = ($this->cached) ? ORM::cache()->get($cacheKey) : false;

        if ($cached !== false && is_array($cached) && defined('CACHE') && CACHE === true) {
            if ($cached === null) {
                return null;
            }
            $res = $this->fillClass($cached, $baseClass);
            return $res;
        }
        //$profile = Logger::start(__CLASS__, __FUNCTION__);
        
        $res = $this->execute();
        $itm = $res->fetch(\PDO::FETCH_ASSOC);
        $result = null;
        if ($itm !== false) {
            $result = $this->fillClass($itm, $baseClass);
        }
        $res->closeCursor();

        if ($itm === false) {
            $itm = null;
        }
        //$q = Logger::start(__CLASS__, 'setCache');
        ORM::cache()->set($cacheKey, $itm, ORM::cache()->defaultLifeTime(), $this->getCacheTags());
        //Logger::stop($q);

        //Logger::stop($profile);
        return $result;
    }

    /**
     * Повертає масив результатів вибірки
     *
     * @param string $baseClass Назва моделі
     *
     * @return array 
     */
    public function fetchAll($baseClass = 'stdClass')
    {
        if (empty($baseClass)) {
            throw new \InvalidArgumentException('baseClass cannot be empty');
        }
        $cacheKey = $this->getCacheKey();
        $cached = ($this->cached) ? ORM::cache()->get($cacheKey) : false;

        // restore cache
        if ($cached !== false && is_array($cached) && defined('CACHE') && CACHE === true) {
            if ($cached === null) {
                return null;
            }
            $res = array();
                 foreach ($cached as &$row) {
                $res []= $this->fillClass($row, $baseClass);
            }
            return $res;
        }
        //$profile = Logger::start(__CLASS__, __FUNCTION__);
        
        $result = false;

        //$this->OnFetchAll($this, $result, $baseClass);

        if ($result === false) {
            $res = $this->execute();
            $itm = $res->fetchAll(\PDO::FETCH_ASSOC);
            $res->closeCursor();
            $result = null;
            $cache = null;
            if ($itm !== false) {
                $result = array();
                $cache = array();
                foreach ($itm as &$row) {
                    $cache []= $row;
                    $result []= $this->fillClass($row, $baseClass);
                }
            }
            
            if ($cache === false) {
                $cache = null;
            }
            // save cache
            ORM::cache()->set($cacheKey, $cache, ORM::cache()->defaultLifeTime(), $this->getCacheTags());
        }

        //Logger::stop($profile);
        return $result;
    }

    /**
     * Повертає к-сть записів в БД для поточного запиту або false якщо к-ть визначити не вдалось
     *
     * @return int|false К-ть записів
     */
    public function rowCount()
    {
        $sql = $this->toSQL();
        $regex = '/^SELECT\s+(?:ALL\s+|DISTINCT\s+)?(?:.*?)\s+FROM\s+(.*)$/i';
        if (preg_match($regex, $sql, $output) > 0) {
            $countQuery = 'SELECT COUNT(*) AS cnt FROM ' . $output[1];
            $regex = '/^(.*)\s+ORDER\s+BY\s+(.*)$/i';
            if (preg_match($regex, $countQuery, $output) > 0) {
                $countQuery = $output[1];
            }
            $regex = '/^(.*)\s+GROUP\s+BY\s+(.*)$/i';
            if (preg_match($regex, $sql, $output) > 0) {
                $countQuery = 'SELECT COUNT(*) AS cnt FROM (' . $countQuery . ') AS t';
            }
            $q = new Query($countQuery, \PDO::FETCH_NUM);
            $q->connection($this->connection());
            return $q->fetch()->cnt;
        }
        return false;
    }

    /**
     * Створює обєкт класу $class і аповнює даними з масиву $data
     *
     * @param array  $data  Дані
     * @param string $class Назва моделі
     *
     * @return mixed 
     */
    protected function fillClass($data, $class)
    {
        if (!class_exists($class)) {
            throw new \Exception('Unknown class ' . $class);
        }
        $resObj = new $class();

        if ($resObj instanceof \Bazalt\ORM\BaseRecord) {
            $resObj->fromArray($data);
        } else {
            foreach ($data as $field => $value) {
                $resObj->$field = $value;
            }
        }
        return $resObj;
    }

    /**
     * Повертає масив інформації про стовпці
     *
     * @return array 
     */
    public function fetchColumnsInfo()
    {
        $res = $this->execute();
        $count = $res->columnCount();
        $info = array();
        for ($i = 0; $i < $count; $i++) {
            $colInfo = $res->getColumnMeta($i);
            $info[$colInfo['name']] = $colInfo;
        }
        return $info;
    }

    /**
     * Повертає масив тегів кешу для запиту
     *
     * @return array Масив тегів
     */
    protected function getCacheTags()
    {
        return $this->cacheTags;
    }

    public function __toString()
    {
        return $this->toSQL();
    }
}
