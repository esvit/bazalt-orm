<?php
/**
 * Insert.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM;

use Bazalt\ORM as ORM;

/**
 * ORM_Exception_Insert
 *
 * @category   System
 * @package    ORM
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Insert extends Base
{
    /**
     * Insert query that generated the exception
     *
     * @var ORM_Query_Insert
     */
    protected $builder = null;

    /**
     * Contructor
     *
     * @param string           $message Exception message
     * @param ORM_Query_Insert $builder Insert query that generated the exception
     * @param Exception        $innerEx Inner exception
     * @param int              $code    Exception code
     */
    public function __construct($message, ORM\Query\Insert $builder, $innerEx = null, $code = 0)
    {
        $this->builder = $builder;

        parent::__construct($message, $innerEx, $code);
    }

    /**
     * Повертає детальну інформацію про помилку
     *
     * @return string
     */
    public function getDetails()
    {
        return 'Builder: ' . get_class($this->builder);
    }
}