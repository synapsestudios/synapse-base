<?php

namespace Synapse\TestHelper;

use ArrayIterator;
use Zend\Db\Adapter\Driver\ResultInterface;

class MockQueryResult extends ArrayIterator implements ResultInterface
{
    /**
     * The value returned by getGeneratedValue
     *
     * @var int
     */
    protected $generatedValue = null;

    /**
     * Force buffering
     *
     * @return void
     */
    public function buffer()
    {
        // Do nothing
    }

    /**
     * Check if is buffered
     *
     * @return bool|null
     */
    public function isBuffered()
    {
        return false;
    }

    /**
     * Is query result?
     *
     * @return bool
     */
    public function isQueryResult()
    {
        return true;
    }

    /**
     * Get affected rows
     *
     * @return int
     */
    public function getAffectedRows()
    {
        return 0; // This mocks a query result, so no rows should be affected
    }

    /**
     * Get generated value
     *
     * @return mixed|null
     */
    public function getGeneratedValue()
    {
        return $this->generatedValue;
    }

    /**
     * Get the resource
     *
     * @return mixed
     */
    public function getResource()
    {
        return null; // Seems we can get away with returning null
    }

    /**
     * Get field count
     *
     * @return int
     */
    public function getFieldCount()
    {
        $arrayCopy = $this->getArrayCopy();

        if (empty($arrayCopy)) {
            return 0;
        }

        return count($arrayCopy[0]);
    }

    /**
     * Set the value returned by getGeneratedValue
     *
     * @param  int $value
     */
    public function setGeneratedValue($value)
    {
        $this->generatedValue = $value;
    }
}
