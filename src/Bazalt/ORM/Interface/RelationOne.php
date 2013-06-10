<?php
use Bazalt\ORM\Record;

/**
 * Record.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Interface
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Interface_RelationOne
 *
 * @category   System
 * @package    ORM
 * @subpackage Interface
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
interface ORM_Interface_RelationOne extends Iterator
{
    /**
     * Get record connected with current record
     *
     * @return Record
     */    
    function get();

    /**
     * Set new record connected with current record
     *
     * @param Record &$item New record
     *
     * @return void
     */    
    function set(Record &$item);
}