<?php

namespace Bazalt\ORM\Relation;

use Framework\Core\Event;
use Bazalt\ORM\Record;
use Bazalt\ORM;

class Many2Many extends AbstractRelation implements IRelationMany
{
    /**
     * Викликається при зверненні до об'єкту зв'язку і повертає масив
     * обєктів зв'язаної моделі, які відносяться до поточного обєкта
     *
     * @param null $limit       Кількість записів, котрі потрібно вибрати
     * @return array
     */
    public function get($limit = null)
    {
        $q = $this->getQuery();
        if(!$q) {
            return array();
        }
        if ($limit !== null) {
            $q->limit((int)$limit);
        }
        return $q->fetchAll($this->name);
    }

    /**
     * Знаходить по $id серед об'єктів звязку
     *
     * @param int $id ід
     *
     * @return Record
     */
    public function getById($id)
    {
        $q = $this->getQuery();
        if(!$q) {
            return null;
        }
        $column = Record::getAutoIncrementColumn($this->name);
        $q->andWhere('ft.'.$column->name().' = ?', (int)$id);
        return $q->fetch($this->name);
    }

    /**
     * Знаходить по $ids серед об'єктів звязку
     *
     * @param array $ids Масив ідентифікаторів
     *
     * @return Record
     */
    public function getByIds($ids)
    {
        $q = $this->getQuery();
        if(!$q) {
            return null;
        }
        $column = Record::getAutoIncrementColumn($this->name);
        $q->andWhereIn('ft.'.$column->name(), $ids);
        return $q->fetchAll($this->name);
    }

    /**
     * Генерує запит для вибірки звязаних обєктів
     *
     * @return SelectQueryBuilder
     */
    public function getQuery()
    {
        $idVal = $this->baseObject->getAutoIncrementValue();
        if (!$idVal) {
            return null;
        }
        $column = Record::getAutoIncrementColumn($this->name);
        $q = ORM::select($this->name . ' ft')
            ->innerJoin($this->refTable . ' ref', array(
                $this->refColumn, 
                'ft.'.$column->name()
            ))
            ->andWhere('ref.' . $this->column . ' = ?', $idVal);

        $this->applyAddParams($q);
        return $q;
    }

    /**
     * Повертає к-ть записів в БД зв'язаної моделі, які відносяться до поточного обєкта
     *
     * @return array
     */
    public function count()
    {
        $q = $this->getQuery();
        if(!$q) {
            return null;
        }
        $q->select('COUNT(*) as `count`', $this->name . ' ft');
        $res = $q->fetch('stdClass');
        return $res->count;
    }

    /**
     * Генерує Sql скрипт для звязку @deprecated
     *
     * @param Record $model Модель до якої йде звязок
     * 
     * @return string
     */
    public function generateSql( $model )
    {
        $ref = array();
        $ref[] = 'ADD CONSTRAINT `'.DataType_String::fromCamelCase($this->refColumn).
                '` FOREIGN KEY (`'.DataType_String::fromCamelCase($this->refColumn).'`) '.
                'REFERENCES `'.DataType_String::fromCamelCase($this->name).'` (`id`) ON DELETE CASCADE';
        $ref[] = 'ADD CONSTRAINT `'.DataType_String::fromCamelCase($this->column).
                '` FOREIGN KEY (`'.DataType_String::fromCamelCase($this->column).'`) '.
                'REFERENCES `'.DataType_String::fromCamelCase($model).'` (`id`) ON DELETE CASCADE';
        $content = 'ALTER TABLE `'.DataType_String::fromCamelCase($this->refTable).'` '."\n".implode(','."\n", $ref).';';

        return array($this->refTable => $content);
    }

    /**
     * Створює зв'язок між поточним обєктом та обєктом $item
     *
     * @param Record $item   Об'єкт, який потрібно додати
     * @param array      $params Масив додаткових значень, які будуть додані в реферальну таблицю
     *
     * @throws Exception
     * @return void
     */
    public function add(Record $item, $params = array())
    {
        $this->checkType($item);

        if ($this->baseObject->isPKEmpty()) {
            throw new Exception('Save item first "' . get_class($this->baseObject) . '"');
        }
        $this->dispatcher()->dispatch('OnAdd', new \Symfony\Component\EventDispatcher\Event($this->baseObject, [$item]));

        if ($item->isPKEmpty()) {
            $item->save();
        }

        $refObj = new $this->refTable();
        $refObj->{$this->column} = $this->baseObject->getAutoIncrementValue();
        $refObj->{$this->refColumn} = $item->getAutoIncrementValue();
        foreach ($params as $key => $param) {
            $refObj->{$key} = $param;
        }
        $refObj->save();
        return $refObj;
    }

    /**
     * Видаляє зв'язок між поточним обєктом та обєктом $item
     *
     * @param Record $item Об'єкт, який потрібно видалити
     *
     * @return void
     */
    public function remove(Record $item)
    {
        $this->checkType($item);

        $this->dispatcher()->dispatch('OnRemove', new \Symfony\Component\EventDispatcher\Event($this->baseObject, [$item]));

        
        $q = ORM::select($this->refTable)
            ->where($this->column.' = ?', $this->baseObject->getAutoIncrementValue())
            ->andWhere($this->refColumn.' = ?', $item->getAutoIncrementValue())
            ->limit(1);
        $obj = $q->fetch();
        if (!$obj) {
            //throw new Exception('Object not found');
            return false;
        }
        $obj->delete();
        return true;
    }

    /**
     * Видаляє всі зв'язки з поточним обєктом
     *
     * @return void
     */   
    public function removeAll()
    {
        $q = ORM::delete($this->refTable)
                ->where($this->column . ' = ?', $this->baseObject->getAutoIncrementValue());
        $q->exec();
    }

    /**
     * Перевіряє чи існує зв'язок між поточним обєктом та обєктом $item
     *
     * @param Record $item Об'єкт, який потрібно перевірити     
     *
     * @return bool
     */
    public function has(Record $item)
    {
        $this->checkType($item);

        $q = ORM::select($this->refTable, 'COUNT(*) as count')
            ->where($this->column.' = ?', $this->baseObject->getAutoIncrementValue())
            ->andWhere($this->refColumn.' = ?', $item->getAutoIncrementValue())
            ->limit(1);
        return (int)$q->fetch('stdClass')->count > 0;
    }
    
    /**
     * Видаляє обєкти і зв'язки, які ще є в БД і не в $ids
     *
     * @param array $ids Об'єкт, який потрібно перевірити
     *
     * @return void
     */
    public function clearByRelations($ids = array())
    {
        $column = Record::getAutoIncrementColumn($this->name);
        $q = $this->getQuery();
        $q->select('ft.' . $column->name() . ' as id', $this->name . ' ft');
        $q->andNotWhereIn('ft.' . $column->name(), $ids);
        $objsToDel = $q->fetchAll('stdClass');

        $idsToDel = array();
        foreach($objsToDel as $objToDel) {
            $idsToDel []= $objToDel->id;
        }

        $this->clearRelations($ids);
        if (count($idsToDel) > 0) {
            $q = ORM::delete($this->name .' r')
                ->andWhereIn('r.'.$column->name(), $idsToDel);
            $q->exec();
        }
    }
    
    /**
     * Видаляє зв'язки, які ще є в БД і не в $ids
     *
     * @param array $ids Об'єкт, який потрібно перевірити     
     *
     * @return void
     */
    public function clearRelations($ids = array())
    {
        $q = ORM::delete($this->refTable .' r')
            ->where('r.'.$this->column.' = ?', $this->baseObject->getAutoIncrementValue());
        if(count($ids) > 0) {
            $q->andNotWhereIn('r.'.$this->refColumn, $ids);
        }
        $q->exec();
    }
}
