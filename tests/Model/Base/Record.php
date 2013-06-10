<?php

use Framework\System\ORM\Record;

/**
 * @codeCoverageIgnore
 */
abstract class ORMTest_Model_Base_Record extends Record
{
    public function getSQLConnectionName()
    {
        return 'test';
    }
}
