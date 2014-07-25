<?php

namespace Synapse\Email;

use Synapse\Command\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use LogicException;
use OutOfBoundsException;

class SendEmailCommand implements CommandInterface
{
    /**
     * @var EmailMapper
     */
    protected $emailMapper;

    /**
     * @var SenderInterface
     */
    protected $emailSender;

    /**
     * Set the email mapper
     *
     * @param EmailMapper $emailMapper
     */
    public function setEmailMapper(EmailMapper $emailMapper)
    {
        $this->emailMapper = $emailMapper;
    }

    /**
     * Set the email sender
     *
     * @param SenderInterface $emailSender
     */
    public function setEmailSender(SenderInterface $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    /**
     * Execute this console command to send an email
     *
     * @param  InputInterface  $input  Command line input interface
     * @param  OutputInterface $output Command line output interface
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $this->emailSender) {
            throw new LogicException('No email sender configured (did you set the Mandrill API key?)');
        }

        if (! $this->emailMapper) {
            throw new LogicException('No email mapper configured');
        }

        $output->writeln('Finding email by ID');

        $emailId = $input->getArgument('id');

        $email = $this->emailMapper->findById($emailId);

        if (!$email or $email->isNew()) {
            throw new OutOfBoundsException('Email not found.');
        }

        $output->writeln('Sending email');

        list($email, $result) = $this->emailSender->send($email);

        if ($email->getStatus() !== EmailEntity::STATUS_SENT) {
            $format = 'Email did NOT send successfully. Returned with status %s.';
            $message = sprintf($format, $result['status']);

            if (isset($result['reject_reason'])) {
                $message .= ' Reason rejected: '.$result['reject_reason'];
            }

            $output->writeln($message);

            return 500;
        }

        $output->writeln('Email sent successfully!');
    }
}
