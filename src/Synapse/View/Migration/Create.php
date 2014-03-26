<?php

namespace Synapse\View\Migration;

use Synapse\View\AbstractView;

/**
 * View for creating new migrations
 */
class Create extends AbstractView
{
    /**
     * Description of the migration
     *
     * @var string
     */
    protected $description;

    /**
     * Name of the migration class
     *
     * @var string
     */
    protected $classname;

    /**
     * Timestamp of when this migration was created
     *
     * @var string
     */
    protected $timestamp;

    /**
     * Set or get the description of the migration
     *
     * @param  string $description Migration description. If omitted, acts as a getter.
     * @return string
     */
    public function description($description = null)
    {
        if ($description === null) {
            return $this->description;
        }

        $this->description = $description;
    }

    /**
     * Set or get the name of the migration class
     *
     * @param  string $classname Name of the migration class. If omitted, acts as a getter.
     * @return string
     */
    public function classname($classname = null)
    {
        if ($classname === null) {
            return $this->classname;
        }

        $this->classname = $classname;
    }

    /**
     * Set or get the timestamp when this migration was created
     *
     * @param  string $timestamp Timestamp when migration was created. If omitted, acts as a getter.
     * @return string
     */
    public function timestamp($timestamp = null)
    {
        if ($timestamp === null) {
            return $this->timestamp;
        }

        $this->timestamp = $timestamp;
    }
}
