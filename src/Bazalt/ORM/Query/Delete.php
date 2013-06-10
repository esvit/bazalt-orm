<?php
/**
 * Delete.php
 *
 * @category   System
 * @package    ORM
 * @subpackage Query
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

namespace Bazalt\ORM\Query;

use Bazalt\ORM as ORM;

/**
 * ORM_Query_Delete
 * Генерує DELETE запит до БД
 *
 * @category   System
 * @package    ORM
 * @subpackage Query
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

class Delete extends Builder
{
    /**
     * Повертає перераховані через кому аліаси таблиць
     *
     * @return string
     */
    protected function getAliases()
    {
        $str = '';
        foreach ($this->from as $k => $i) {
            if (!is_numeric($k)) {
                $str .=  $k;
            }
            $str .= ', ';
        }
        return substr($str, 0, -2);
    }
    
    /**
     * Генерує SQL для запиту
     *
     * @return string
     */
    public function buildSQL()
    {
        ORM::cache()->removeByTags($this->getCacheTags());

        $query  = 'DELETE ' . $this->getAliases() . ' ';
        $query .= 'FROM ' . $this->getFrom() . ' ';
        if (count($this->joins) > 0) {
            foreach ($this->joins as $join) {
                $query .= ' ' . $join->toSQL();
            }
        }
        if (!empty($this->where)) {
            $query .= 'WHERE ' . $this->where . ' ';
        }
        return $query;
    }
}