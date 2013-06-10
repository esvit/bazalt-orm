<?php
/**
 * Table.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM\Exception;

/**
 * ORM_Exception_Table
 *
 * @category   System
 * @package    ORM
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Table extends Base
{
    /**
     * Table that generated the exception
     *
     * @var string
     */
    protected $tableName = null;

    /**
     * Contructor
     *
     * @param string    $message   Exception message
     * @param string    $tableName Table that generated the exception
     * @param Exception $innerEx   Inner exception
     * @param int       $code      Exception code
     */
    public function __construct($message, $tableName, $innerEx = null, $code = 0)
    {
        $this->tableName = $tableName;

        parent::__construct($message, $innerEx, $code);
    }

    /**
     * Повертає детальну інформацію про помилку
     *
     * @return string
     */ 
    public function getDetails()
    {
        return 'Table name: ' . $this->tableName;
    }
}