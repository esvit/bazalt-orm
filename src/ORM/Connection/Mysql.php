<?php
/**
 * Mysql.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Connection
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM\Connection;

use Framework\Core\Logger,
    Bazalt\ORM as ORM;

/**
 * ORM_Connection_Mysql
 *
 * @category   System
 * @package    ORM
 * @subpackage Connection
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class Mysql extends AbstractConnection
{
    /**
     * Генерує SELECT SQL запиту, орієнтований на нюанси реалізації MySQL
     *
     * @param ORM_Query_Builder $builder Об'єкт запиту
     *
     * @return string SQL запит
     */
    public function buildSelectSQL(ORM\Query\Builder $builder)
    {
        $query  = 'SELECT ';

        if ($builder->PageNum != null) {
            $query .= 'SQL_CALC_FOUND_ROWS ';
        }
        $query .= '' . implode(',', $builder->Select) . ' ';
        $query .= 'FROM ' . $builder->From . ' ';
        if (count($builder->Joins) > 0) {
            foreach ($builder->Joins as $join) {
                $query .= ' ' . $join->toSQL();
            }
        }

        $where = $builder->Where;
        if (!empty($where)) {
            $query .= 'WHERE ' . $where . ' ';
        }

        $groupBy = $builder->GroupBy;
        if (count($groupBy) > 0) {
            $query .= 'GROUP BY ' . implode(',', $groupBy) . ' ';
        }

        $having = $builder->Having;
        if (!empty($having)) {
            $query .= 'HAVING ' . $having . ' ';
        }

        $orderBy = $builder->OrderBy;
        if (count($orderBy) > 0) {
            $query .= 'ORDER BY ' . implode(',', $orderBy) . ' ';
        }

        $limitCount = $builder->LimitCount;
        $limitFrom = $builder->LimitFrom;
        if (isset($limitFrom)) {
            $query .= 'LIMIT ' . $limitFrom . (isset($limitCount) ? ', '.$limitCount : '' );
        }
        return $query;
    }

    /**
     * Екранує значення $string
     *
     * @param $string Строка для екранування
     *
     * @return $string Екранована строка
     */
    public function quote($string)
    {
        return '`' . $string . '`';
    }
}