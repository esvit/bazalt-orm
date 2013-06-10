<?php
/**
 * Unique.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Index
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Index_Unique
 *
 * @category   System
 * @package    ORM
 * @subpackage Index
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class ORM_Index_Unique extends ORM_Index_Abstract
{
    /**
     * Повертає SQL для Create Table
     *
     * @return string 
     */
    public function toSql()
    {
        return 'UNIQUE '.parent::toSql();
    }
}
