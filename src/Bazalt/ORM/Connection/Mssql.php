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

/**
 * ORM_Connection_Mssql
 *
 * @category   System
 * @package    ORM
 * @subpackage Connection
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class ORM_Connection_Mssql extends ORM_Connection_Abstract
{
    /**
     * Генерує SELECT SQL запиту, орієнтований на нюанси реалізації MSSQL
     *
     * @param ORM_Query_Builder $builder Об'єкт запиту
     *
     * @return string SQL запит
     */
    public function buildSelectSQL(ORM_Query_Builder $builder)
    {
        $query  = 'SELECT ';

        $limitCount = $builder->LimitCount;
        $limitFrom = $builder->LimitFrom;
        if (isset($limitCount) && !isset($limitFrom)) {
            $query .= 'TOP ' . $limitCount . (isset($limitCount) ? /*' TO '.$builder->limitCount*/'' : '' ) . ' ';
        }

        if ($builder->PageNum != null) {
            $query .= 'SQL_CALC_FOUND_ROWS ';
        }
        $query .= '' . implode(',', $builder->Select) . ' ';

        $orderBy = $builder->OrderBy;
        if (count($orderBy) > 0) {
            $orderBy = implode(',', $orderBy);
        }
        if (isset($limitCount) && isset($limitFrom)) {
            $query .= ', ROW_NUMBER() OVER(ORDER BY ' . ($orderBy ? $orderBy : 'id') . ') AS orm__rowNumber ';
        }

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

        if ($orderBy) {
            $query .= 'ORDER BY ' . $orderBy . ' ';
        }

        if (isset($limitCount) && isset($limitFrom)) {
            $query = 'SELECT * FROM (' . $query . ') AS t WHERE orm__rowNumber BETWEEN ' . ($limitFrom + 1) . ' AND ' . ($limitFrom + $limitCount);
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
        return '"' . $string . '"';
    }


    /**
     * Повертає ід останнього вставленого рядка з БД або null
     *
     * @return $string Ід
     */
    public function getLastInsertId()
    {
        $q = new ORM_Query('SELECT @@IDENTITY as last_id');
        $q->connection($this);
        $res = $q->fetch();
        if (!$res) {
            return null;
        }
        return $res->last_id;
    }
}