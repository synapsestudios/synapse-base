<?php

namespace Synapse\Email;

use Synapse\Email\Mapper\Email as EmailMapper;
use Synapse\Email\Entity\Email as EmailEntity;
use Synapse\Resque\Resque;
use Synapse\Stdlib\Arr;

/**
 * General purpose service for handling email entities
 */
class EmailService
{
    /**
     * @var EmailMapper
     */
    protected $emailMapper;

    /**
     * @var array
     */
    protected $emailConfig;

    /**
     * @var Resque
     */
    protected $resque;

    /**
     * @param EmailMapper $mapper
     */
    public function setEmailMapper(EmailMapper $mapper)
    {
        $this->emailMapper = $mapper;
        return $this;
    }

    /**
     * @param array $config
     */
    public function setEmailConfig(array $config)
    {
        $this->emailConfig = $config;
        return $this;
    }

    /**
     * @param Resque $resque
     */
    public function setResque(Resque $resque)
    {
        $this->resque = $resque;
    }

    /**
     * Create an email entity from an array and populate with default data
     *
     * @param  array  $data Data to populate the email
     * @return Email
     */
    public function createFromArray(array $data)
    {
        $headers = json_encode(
            Arr::path($this->emailConfig, 'defaults.headers', [])
        );

        $defaults = [
            'headers'      => $headers,
            'sender_email' => Arr::path($this->emailConfig, 'defaults.sender.email'),
            'sender_name'  => Arr::path($this->emailConfig, 'defaults.sender.name'),
        ];

        $email = new EmailEntity;

        $email = $email->fromArray(
            array_merge($defaults, $data)
        );

        $email = $this->emailMapper->persist($email);

        return $email;
    }

    /**
     * Queue a job to send an email
     *
     * @param  EmailEntity $email
     * @return string
     */
    public function enqueueSendEmailJob(EmailEntity $email)
    {
        return $this->resque->enqueue(
            'email',
            'Synapse\\Work\\Email\\Send',
            ['id' => $email->getId()]
        );
    }
}
