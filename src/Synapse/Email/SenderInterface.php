<?php

namespace Synapse\Email;

interface SenderInterface
{
    /**
     * Send an email
     *
     * @param  Email  $email
     * @return mixed         Result of attempt to send email
     */
    public function send(EmailEntity $email);
}
