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
    public static function getException(\PDOException $e, $query = null, $params = array())
    {
        switch ($e->getCode()) {
        case 1049:
            return new Database($e->getMessage());
        }
        return new Query($e, $query, $params);
    }
}