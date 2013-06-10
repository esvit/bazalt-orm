<?php
/**
 * NestedSet.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Exception_NestedSet
 *
 * @category   System
 * @package    ORM
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class ORM_Exception_NestedSet extends ORM_Exception_Base
{
    /**
     * Errors list
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Contructor
     *
     * @param array      $messages Exception messages
     * @param Exception  $innerEx  Inner exception
     * @param int        $code     Exception code
     */
    public function __construct($messages, $innerEx = null, $code = 0)
    {
        $this->errors = $messages;
        $message = implode("\n", $messages);
        parent::__construct($message, $innerEx, $code);
    }
}