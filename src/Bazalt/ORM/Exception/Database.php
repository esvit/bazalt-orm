<?php

namespace Bazalt\ORM\Exception;

class Database extends Base
{
    /**
     * @var string
     */
    protected $database = null;

    /**
     * Contructor
     *
     * @param string     $message Exception message
     * @param \Exception $innerEx Inner exception
     * @param int        $code    Exception code
     */
    public function __construct($message, $innerEx = null, $code = 0)
    {
        preg_match('/SQLSTATE\[(\w+)\](: (.*): (\d+) | \[(\w+)\] )?(.*)\'(.*)\'(.*)/', $message, $matches);

        $code = $matches[5];
        $message = trim($matches[6]) . ' "' . $matches[7] . '"';
        $this->database = $matches[7];

        parent::__construct($message, $innerEx, $code);
    }

    /**
     * Повертає детальну інформацію про помилку
     *
     * @return string
     */ 
    public function getDetails()
    {
        return 'Database: ' . $this->database;
    }
}