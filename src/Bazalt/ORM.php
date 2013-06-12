<?php
/**
 * ORM.php
 *
 * @category   System
 * @package    ORM
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt;

if (!defined('DEVELOPMENT_STAGE')) {
    define('STAGE', 'dev');
    define('DEVELOPMENT_STAGE', 'dev');
}

if (!extension_loaded('pdo_mysql')) {
    throw new Exception('PHP Extension "pdo_mysql" must be loaded');
}

/**
 * ORM
 *
 * @category   System
 * @package    ORM
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class ORM
{
    /**
     * Тип обєднання UNION - 'ALL', в результаті обєднання будуть всі записи
     */
    const UNION_ALL = 'ALL';

    /**
     * Тип обєднання UNION - 'DISTINCT', в результаті обєднання будуть тільки унікальні записи
     */
    const UNION_DISTINCT = 'DISTINCT';

    public static function cache()
    {
        return new ORM\CacheAdapter();
    }

    public static function logger($class)
    {
        $logger = new \Analog\Logger();
        $logger->handler(\Analog\Handler\Stderr::init());
        return $logger;
    }

    /**
     * Створює новий SELECT запит до БД за допомогою ORM\Query\Select
     *
     * @param string $from   Назва моделі
     * @param string $fields Список полів моделі, розділених комою
     *
     * @return ORM\Query\Select
     */
    public static function select($from = null, $fields = null)
    {
        $builder = new ORM\Query\Select();
        if ($from != null) {
            if (strpos($from, ' ') === false) {
                $from .= ' f';
            }
            $builder->from($from);
        }
        if ($fields != null) {
            $builder->select($fields);
        }
        return $builder;
    }

    /**
     * Створює новий DELETE запит до БД за допомогою ORM\Query\Delete
     *
     * @param string $from Назва моделі
     *
     * @return ORM\Query\Delete
     */
    public static function delete($from = null)
    {
        $builder = new ORM\Query\Delete();
        if ($from != null) {
            $builder->from($from);
        }
        return $builder;
    }

    /**
     * Створює новий INSERT запит до БД за допомогою ORM_Query_Insert
     *
     * @param string $from Назва моделі
     * @param string $set  Об'єкт ORM\Record
     *
     * @throws ORM\Exception\Insert
     * @return ORM\Query\Insert
     */
    public static function insert($from, $set = null)
    {
        $builder = new ORM\Query\Insert();
        if ($from == 'DUAL' || !$from) {
            throw new ORM\Exception\Insert('INTO parameter not set', $builder);
        }
        $builder->from($from);

        if ($set != null) {
            $builder->set($set);
        }
        return $builder;
    }

    /**
     * Створює новий UPDATE запит до БД за допомогою ORM_Query_Update
     *
     * @param string $model Назва моделі
     * @param string $set  Об'єкт ORMRecord
     *
     * @throws ORM\Exception\Model
     * @return ORM\Query\Update
     */
    public static function update($model, $set = null)
    {
        $builder = new ORM\Query\Update();
        if (!$model) {
            throw new ORM\Exception\Model('Model parameter not set', $model);
        }
        $builder->from($model);

        if ($set != null) {
            $builder->set($set);
        }
        return $builder;
    }

    /**
     * Обєднує результати двох запитів
     *
     * @param \ORM_Query_Builder $query1 Запит для обєднання
     * @param \ORM_Query_Builder $query2 Запит для обєднання
     * @param string            $type   Тип обднання
     *
     * @return ORM\Query\Union
     */
    public static function union(ORM\Query\Builder $query1, ORM\Query\Builder $query2, $type = self::UNION_DISTINCT)
    {
        $builder = new ORM\Query\Union($query1, $query2);
        if ($type != self::UNION_DISTINCT) {
            $builder->all();
        }
        return $builder;
    }

    /**
     * Перевір'яє чи існує таблиця в БД
     *
     * @param string $name Назва таблиці
     *
     * @return bool
     */
    public static function isTableExists($name)
    {
        $q = new ORM\Query('SHOW TABLES LIKE "' . $name . '";');
        return $q->fetch() != null;
    }

    /**
     * Видаляє таблицю з БД
     *
     * @param string $name Назва таблиці
     *
     * @return void
     */
    public static function dropTable($name)
    {
        $q = new ORM\Query('DROP TABLE IF EXISTS `' . $name . '`;');
        $q->exec();
    }

    /**
     * Розпочинає транзакцію
     *
     * @return void
     */
    public static function begin()
    {
        ORM\Connection\Manager::getConnection()->begin();
    }

    /**
     * Комітить транзакцію
     *
     * @return void
     */
    public static function commit()
    {
        ORM\Connection\Manager::getConnection()->commit();
    }

    /**
     * Робить відкат змін в межах розпочатої транзакції
     *
     * @return void
     */
    public static function rollBack()
    {
        ORM\Connection\Manager::getConnection()->rollBack();
    }
}