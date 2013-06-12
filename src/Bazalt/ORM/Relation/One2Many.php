<?php

namespace Bazalt\ORM\Relation;

use Bazalt\ORM as ORM;

class One2Many extends AbstractRelation implements IRelationMany
{
    /**
     * Constructor
     *
     * @param string $name             Назва моделі до якої іде звязок
     * @param string $column           Назва поля (стовпця) моделі від якої йде звязок
     * @param string $refColumn        Назва поля (стовпця) моделі до якої йде звязок     
     * @param string $additionalParams Масив додаткових параметрів, 
     *                                 які будуть враховуватись при вибірках по звязку
     */
    public function __construct($name, $column, $refColumn, $additionalParams = null)
    {
        $this->name = $name;
        $this->column = $column;
        $this->refColumn = $refColumn;
        $this->additionalParams = $additionalParams;
    }
    
    /**
     * Викликається при зверненні до об'єкту зв'язку
     * і повертає масив обєктів звязаної моделі, які відносяться до поточного обєкта
     *
     * @param null $limit       Кількість записів, котрі потрібно вибрати
     * @return array
     */
    public function get($limit = null)
    {
        $q = $this->getQuery();
        if (!$q) {
            return null;
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
        $c = $this->column;
        if (!isset($this->baseObject->$c)) {
            //throw new Exception(sprintf('Field %s of model %s is not set', $c, get_class($this->baseObject)));
            return null;
        }

        $idVal = $this->baseObject->$c;        
        $q = ORM::select($this->name . ' ft')
            ->andWhere('ft.' . $this->refColumn . ' = ?', $idVal);
        $this->applyAddParams($q);
        return $q;
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
        $name = array($model,$this->name);
        sort($name);
        $ref = array();
        $ref[] = 'ADD KEY `'.Record::getTableName($this->name).'_'.DataType_String::fromCamelCase($this->refColumn).
                 '` (`'.DataType_String::fromCamelCase($this->refColumn).'`)';
        $ref[] = 'ADD CONSTRAINT `'.Record::getTableName($this->name).'_'.DataType_String::fromCamelCase($this->refColumn).
                 '` FOREIGN KEY (`'.DataType_String::fromCamelCase($this->refColumn).'`) REFERENCES `'.
                 DataType_String::fromCamelCase($model).'` (`'.DataType_String::fromCamelCase($this->column).'`) ON DELETE CASCADE';
        $content = 'ALTER TABLE `'.Record::getTableName($this->name).'` '."\n".implode(','."\n", $ref).';'; 
        return array( implode('_', $name) => $content ); 
    }

    /**
     * Створює зв'язок між поточним обєктом та обєктом $item
     *
     * @param Record $item Об'єкт, який потрібно додати
     *
     * @return void
     */
    public function add(\Bazalt\ORM\Record $item)
    {
        $this->checkType($item);

        $this->dispatcher()->dispatch('OnAdd', new \Symfony\Component\EventDispatcher\Event($this->baseObject, [$item]));
        
        $item->{$this->refColumn} = $this->baseObject->{$this->column};
        $item->save();
    }
    
    /**
     * Видаляє всі об'єкти по зв'язку
     *
     * @return void
     */   
    public function removeAll()
    {
        $q = ORM::delete($this->name)
                ->where($this->refColumn . ' = ?', $this->baseObject->id);

        $q->exec();
    }

    /**
     * Видаляє зв'язок між поточним обєктом та обєктом $item
     *
     * @param Record $item Об'єкт, який потрібно видалити
     *
     * @return void
     */
    public function remove(\Bazalt\ORM\Record $item)
    {
        throw new DontDevelopedYetException();
    }

    /**
     * Перевіряє чи існує зв'язок між поточним обєктом та обєктом $item
     *
     * @param Record $item Об'єкт, який потрібно перевірити     
     *
     * @return bool
     */ 
    public function has(\Bazalt\ORM\Record $item)
    {
        $this->checkType($item);
        
        return (bool)($item->{$this->refColumn} == $this->baseObject->{$this->column});
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
        $q = ORM::delete($this->name)
            ->where($this->refColumn . ' = ?', $this->baseObject->id);
        if(count($ids) > 0) {
            $q->andNotWhereIn($this->column, $ids);
        }
        $q->exec();
    }
}
