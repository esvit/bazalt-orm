<?php

namespace Bazalt\ORM\Relation;
use Bazalt\ORM\Record;

interface IRelationOne extends \Iterator
{
    /**
     * Get record connected with current record
     *
     * @return Record
     */    
    function get();

    /**
     * Set new record connected with current record
     *
     * @param Record &$item New record
     *
     * @return void
     */    
    function set(Record &$item);
}