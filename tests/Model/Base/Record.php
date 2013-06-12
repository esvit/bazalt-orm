<?php

namespace tests\Model\Base;

/**
 * @codeCoverageIgnore
 */
abstract class Record extends \Bazalt\ORM\Record
{
    public function getSQLConnectionName()
    {
        return 'test';
    }
}
