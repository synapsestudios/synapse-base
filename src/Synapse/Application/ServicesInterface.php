<?php

namespace Synapse\Application;

use Synapse\Application;

/**
 * Defines an interface for a class whose responsibility is to register services for the application
 */
interface ServicesInterface
{
    /**
     * Register services for the application
     *
     * @param  Application $app
     */
    public function register(Application $app);
}
