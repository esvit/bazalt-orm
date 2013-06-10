<?php
/**
 * Model.php
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
 * ORM_Exception_Model
 *
 * @category   System
 * @package    ORM
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Model extends Base
{
    /**
     * Model that generated the exception
     *
     * @var ORM_Record
     */
    protected $model = null;

    /**
     * Contructor
     *
     * @param string     $message Exception message
     * @param ORM_Record $model   Model that generated the exception
     * @param Exception  $innerEx Inner exception
     * @param int        $code    Exception code
     */
    public function __construct($message, $model, $innerEx = null, $code = 0)
    {
        $this->model = $model;

        parent::__construct($message, $innerEx, $code);
    }

    /**
     * Повертає детальну інформацію про помилку
     *
     * @return string
     */ 
    public function getDetails()
    {
        return 'Model: ' . get_class($this->model);
    }
}