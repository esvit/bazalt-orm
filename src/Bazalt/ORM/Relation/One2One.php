<?php

namespace Bazalt\ORM\Relation;

class One2One extends AbstractRelation implements IRelationOne
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
     * і повертає об`єкт звязаної моделі, який відносяться до поточного об'єкта
     *
     * @return ORM_Record
     */
    public function get()
    {
        $q = $this->getQuery();
        if ($q == null) {
            return null;
        }
        return $this->getQuery()->fetch($this->name);
    }

    /**
     * Встановлює зв'язок між поточним обєктом та обєктом $item
     * 
     * @param Record $item Об'єкт, який потрібно додати
     *
     * @return void
     */
    public function set(\Bazalt\ORM\Record &$item)
    {
        $this->baseObject->setField($this->column, $item->{$this->refColumn});

        if ($this->baseObject->isPKEmpty()) {
            return;
        }
        $id = null;
        if ($item != null) {
            if ($item->isPKEmpty()) {
                $item->save();
            }
            $id = $item->{$this->refColumn};
        }
        $q = \Bazalt\ORM::update(get_class($this->baseObject) . ' ft')
                ->set($this->column, $id);

        $pKeys = \Bazalt\ORM\Record::getPrimaryKeys(get_class($this->baseObject));

        foreach ($pKeys as $key) {
            $q->andWhere($key->name() . ' = ?', $this->baseObject->{$key->name()});
        }

        $this->applyAddParams($q);

        $q->exec();
    }

    /**
     * Генерує запит для вибірки звязаних обєктів
     *
     * @return ORM_Query_Select
     */
    public function getQuery()
    {
        $c = $this->column;
        if (!isset($this->baseObject->$c)) {
            return null;
        }
        $idVal = $this->baseObject->$c;
        $q = \Bazalt\ORM::select($this->name . ' ft')
                ->andWhere('ft.' . $this->refColumn . ' = ?', $idVal)
                ->limit(1);
        $this->applyAddParams($q);

        return $q;    
    }

    /**
     * Генерує Sql скрипт для звязку @deprecated
     *
     * @param ORM_Record $model Модель до якої йде звязок
     * @codeCoverageIgnore
     * 
     * @return string
     */
    public function generateSql($model)
    {
        $name = array($model,$this->name);
        sort($name);

        $ref = array();
        $ref[] = 'ADD UNIQUE KEY `'.ORM_Record::getTableName($this->name).'_'.
                 DataType_String::fromCamelCase($this->refColumn).'` (`'.DataType_String::fromCamelCase($this->refColumn).'`)';
        $ref[] = 'ADD CONSTRAINT `'.ORM_Record::getTableName($this->name).'_'.
                 DataType_String::fromCamelCase($this->refColumn).'` FOREIGN KEY (`'.
                 DataType_String::fromCamelCase($this->refColumn).'`) REFERENCES `'.DataType_String::fromCamelCase($model).'` (`'.
                 DataType_String::fromCamelCase($this->column).'`) ON DELETE CASCADE';        
        $content = 'ALTER TABLE `'.ORM_Record::getTableName($this->name).'` '."\n".implode(','."\n", $ref).';'; 
        return array( implode('_', $name) => $content ); 
    }
}
