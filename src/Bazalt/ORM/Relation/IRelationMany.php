<?php

namespace Bazalt\ORM\Relation;
use Bazalt\ORM\Record;

interface IRelationMany extends \Iterator
{
    /**
     * Створює зв'язок між поточним обєктом та обєктом $item
     *
     * @param Record $item об'єкт, який потрібно додати
     *
     * @return void
     */
    function add(Record $item);

    /**
     * Видаляє зв'язок між поточним обєктом та обєктом $item
     *
     * @param Record $item об'єкт, який потрібно видалити
     *
     * @return void
     */    
    function remove(Record $item);

    /**
     * Перевіряє чи існує зв'язок між поточним обєктом та обєктом $item
     *
     * @param Record $item об'єкт, який потрібно перевірити
     *
     * @return bool
     */       
    function has(Record $item);
}