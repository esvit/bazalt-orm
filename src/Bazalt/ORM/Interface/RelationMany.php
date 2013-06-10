<?php
use Bazalt\ORM\Record;

/**
 * RelationMany.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Interface
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Interface_RelationMany
 *
 * @category   System
 * @package    ORM
 * @subpackage Interface
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
interface ORM_Interface_RelationMany extends Iterator
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