<?php

namespace Bazalt\ORM;

use Bazalt\ORM;

/**
 * ORM_Record
 * Реалізація патерну Active record pattern 
 * @link http://en.wikipedia.org/wiki/Active_record_pattern
 *
 * @category   System
 * @package    ORM
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
abstract class Record extends BaseRecord
{
    /**
     * Повертає обєкт з БД по первинному ключу ( ід )
     *
     * @param integer $id    Значення первинного ключа моделі
     * @param string  $class = null Назва моделі
     *
     * @throws \ORM_Exception_Table
     * @throws \InvalidArgumentException
     * @return Record
     */
    public static function getRecordById($id, $class = null)
    {
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException();
        }

        $className = is_null($class) ? getCalledClass() : $class;

        $field = self::getAutoIncrementColumn($className);
        if ($field) {
            // if model has autoincrement field
            $q = ORM::select($className . ' f')
                ->andWhere('f.' . $field->name().' = ?', $id)
                ->limit(1);

            return $q->fetch($className);
        }

        $keys = self::getPrimaryKeys($className);
        if (count($keys) == 0) {
            throw new \ORM_Exception_Table('This model havent primary keys', $class);
        }
        if (!is_array($id) && count($keys) == 1) {
            $akeys = array_keys($keys);
            $id = array($akeys[0] => $id);
        }
        $q = ORM::select($className . ' f')->limit(1);
        foreach ($keys as $field) {
            if (!isset($id[$field->name()])) {
                throw new \InvalidArgumentException('Parameter ' . $field->name() . ' not found');
            }
            $q->andWhere('f.' . $field->name().' = ?', $id[$field->name()]);
        }

        return $q->fetch($className);
    }
    
    /**
     * Повертає всі обєкти з БД
     *
     * @param integer|null $limit Ліміт
     * @param string       $class Назва моделі
     *
     * @return array
     */
    public static function getAllRecords($limit = null, $class = null)
    {
        $className = is_null($class) ? getCalledClass() : $class;

        $q = ORM::select($className . ' f');
        if (!is_null($limit)) {
            $q->limit($limit);
        }

        return $q->fetchAll($className);
    }

    /**
     * Оновлює або створює новий запис в БД
     *
     * @return void
     */
    public function save()
    {
        $return = false;
        $this->checkEvent(self::ON_RECORD_SAVE, $return);
        if ($return) {
            return;
        }

        $className = get_class($this);
        $column = self::getAutoIncrementColumn($className);

        $res = false;
        if (!$this->isPKEmpty()) {
            $pKeys = self::getPrimaryKeys(get_class($this));
            $q = ORM::select($className, 'COUNT(*) AS cnt');
            foreach ($pKeys as $pKeyName => $pKey) {
                $q->andWhere($pKeyName . ' = ?', $this->{$pKeyName});
            }
            $count = $q->fetch('stdClass');
            if ($count && $count->cnt > 0) {
                $res = true;
                $q = ORM::update($className, $this);
                $q->noCache();
                $q->exec();
            }
        }

        if (!$res) {
            $pKeys = self::getPrimaryKeys(get_class($this));
            $q = ORM::insert($className, $this);
            $q->noCache();
            $q->exec();

            if (!is_null($column)) {
                $fieldName = $column->name();

                $id = $q->Connection->getLastInsertId();
                if (empty($this->$fieldName) && $id > 0) {
                    $this->$fieldName = $id;
                }
            }
        }
        $this->checkEvent(self::ON_AFTER_RECORD_SAVE);
    }

    /**
     * Перевіряє чи пусте значення первичного ключа в об'єкті, по суті перевіряє чи існує він в БД
     *
     * @return bool
     */
    public function isPKEmpty()
    {
        $pKeys = self::getPrimaryKeys(get_class($this));

        foreach ($pKeys as $pKeyName => $pKey) {
            if (empty($this->{$pKeyName})) {
                return true;
            }
        }
        return false;
    }

    /**
     * Видаляє запис з БД
     *
     * @param integer|null $id = null Значення первинного ключа моделі
     *
     * @return integer Кількість задіяних рядків
     */
    public function delete($id = null)
    {
        $this->checkEvent(self::ON_RECORD_DELETE);

        $className = get_class($this);
        
        $field = self::getAutoIncrementColumn($className);

        $builder = ORM::delete($className);

        if (!is_null($field)) {
            $fieldName = $field->name();
            if (!empty($this->$fieldName)) {
                $builder->where($fieldName . ' = ?', $this->$fieldName);
            } elseif (!is_null($id)) {
                $builder->where($fieldName . ' = ?', $id);
            }
        } else {
            //Якщо такого поля немає - перевіряємо первинні ключі моделі
            $pKeys = self::getPrimaryKeys($className);

            if (count($pKeys) == 0) {
                throw new DontDevelopedYetException();
            }

            foreach ($pKeys as $pKeyName => $pKey) {
                $builder->andWhere($pKeyName . ' = ?', $this->$pKeyName);   
            }
        }
        
        $builder->exec();
        return $builder->rowCount();
    }

    public static function getById($id)
    {
        return self::getRecordById($id, get_called_class());
    }

    public static function getAll($limit = null)
    {
        return self::getAllRecords($limit, get_called_class());
    }

    public static function select($fields = null)
    {
        return ORM::select(get_called_class(), $fields);
    }

    public static function insert($fields = null)
    {
        return ORM::insert(get_called_class(), $fields);
    }
}