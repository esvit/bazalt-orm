<?php

namespace Bazalt\ORM\Exception;

class NestedSet extends Base
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