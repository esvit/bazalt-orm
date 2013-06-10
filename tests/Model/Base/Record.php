<?php

use Framework\System\ORM\Record;

/**
 * @codeCoverageIgnore
 */
abstract class tests\Model\Base_Record extends Record
{
    public function getSQLConnectionName()
    {
        return 'test';
    }
}
