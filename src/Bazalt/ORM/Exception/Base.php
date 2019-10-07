<?php
/**
 * Base.php
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
 * ORM_Exception_Base
 *
 * @category   System
 * @package    ORM
 * @subpackage Exception
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
abstract class Base extends \Exception
{
    /**
     * Contructor
     *
     * @param string $message Exception message
     * @param \Exception $innerEx Inner exception
     * @param int $code Exception code
     */
    public function __construct($message, $innerEx = null, $code = 0)
    {
        parent::__construct($message, $code, $innerEx);
    }

    public static function getException(\PDOException $e, $query = null, $params = array())
    {
        switch ($e->getCode()) {
            case 1049:
                return new Database($e->getMessage());
            case 1213:
            case 40001:
                return new Deadlock($e->getMessage(), $e, $e->getCode());
        }
        return new Query($e, $query, $params);
    }
}
