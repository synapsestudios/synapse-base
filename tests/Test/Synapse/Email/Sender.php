<?php

namespace Test\Synapse\Email;

use Synapse\Email\AbstractSender;
use Synapse\Email\EmailEntity;

/**
 * Class intended for testing Synapse\Email\AbstractSender
 */
class Sender extends AbstractSender
{
    /**
     * {@inheritdoc}
     */
    public function send(EmailEntity $email)
    {
    }

    /**
     * Proxy for filterThroughWhitelist for testing purposes
     *
     * @param  string $email Email address
     * @return string        Filtered email address
     */
    public function getFilteredEmailAddress($email)
    {
        return $this->filterThroughWhitelist($email);
    }
}
