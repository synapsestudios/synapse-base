<?php

namespace Synapse\Application;

use Synapse\Application;

/**
 * Defines an interface for a class whose responsibility is to define routes for the application
 */
interface RoutesInterface
{
    /**
     * Define routes for the application
     *
     * @param  Application $app
     */
    public function define(Application $app);
}
