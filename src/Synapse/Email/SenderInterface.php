<?php

namespace Synapse\Email;

use Synapse\Entity\AbstractEntity;

interface SenderInterface
{
    /**
     * Send an email
     *
     * @param  Email  $email
     * @return mixed         Result of attempt to send email
     */
    public function send(AbstractEntity $email);
}
