<?php

namespace Synapse\Email;

use Synapse\Email\Entity\Email;

interface SenderInterface
{
    /**
     * Send an email
     *
     * @param  Email  $email
     * @return mixed         Result of attempt to send email
     */
    public function send(Email $email);
}
