<?php

namespace Bazalt\ORM\Exception;

class Table extends Base
{
    /**
     * Table that generated the exception
     *
     * @var string
     */
    protected $tableName = null;

    /**
     * Contructor
     *
     * @param string    $message   Exception message
     * @param string    $tableName Table that generated the exception
     * @param Exception $innerEx   Inner exception
     * @param int       $code      Exception code
     */
    public function __construct($message, $tableName, $innerEx = null, $code = 0)
    {
        $this->tableName = $tableName;

        parent::__construct($message, $innerEx, $code);
    }

    /**
     * Повертає детальну інформацію про помилку
     *
     * @return string
     */ 
    public function getDetails()
    {
        return 'Table name: ' . $this->tableName;
    }
}