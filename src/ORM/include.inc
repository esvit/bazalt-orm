<?php
/**
 * include.inc
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

if (!extension_loaded('pdo_mysql')) {
    throw new Exception('PHP Extension "pdo_mysql" must be loaded');
}

Framework\Core\Autoload::registerNamespace('ORM', dirname(__FILE__));

using('Framework.System.Cache');

require_once 'ORM.php';