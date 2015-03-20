<?php

namespace Synapse\Time;

class Time
{
    public function date()
    {
        return call_user_func_array('date', func_get_args());
    }

    public function time()
    {
        return time();
    }
}
