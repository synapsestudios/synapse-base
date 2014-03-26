<?php

namespace Synapse\Work\Email;

use Synapse\Application;
use Synapse\Work\AbstractConsoleWork;

/**
 * Work for sending emails
 */
class Send extends AbstractConsoleWork
{
    /**
     * {@inheritDoc}
     */
    protected function getConsoleCommand(Application $app)
    {
        return $app['email.send'];
    }
}
