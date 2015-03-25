<?php

namespace Synapse\Time;

trait TimeAwareTrait
{
    protected $time;

    public function setTimeObject(Time $time)
    {
        $this->time = $time;
    }
}
