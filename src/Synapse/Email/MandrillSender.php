<?php

namespace Synapse\Email;

use Synapse\Email\Entity\Email;
use Synapse\Email\Mapper\Email as EmailMapper;
use Mandrill;

/**
 * Service to send emails
 */
class MandrillSender implements SenderInterface
{
    /**
     * @var Mandrill
     */
    protected $mandrill;

    /**
     * @var Synapse\Mapper\Email
     */
    protected $mapper;

    /**
     * @param Mandrill             $mandrill
     * @param Synapse\Mapper\Email $mapper
     */
    public function __construct(Mandrill $mandrill, EmailMapper $mapper)
    {
        $this->mandrill = $mandrill;
        $this->mapper   = $mapper;
    }

    /**
     * {@inheritDoc}
     */
    public function send(Email $email)
    {
        $time = time();

        $message = $this->buildMessage($email);

        $result = $this->mandrill->messages->send($message);
        $result = array_shift($result);

        $email->setStatus($result['status']);
        $email->setDateSent($time);
        $email->setDateUpdated($time);

        $this->mapper->update($email);

        return [$email, $result];
    }

    /**
     * Build Mandrill compatible message array from email entity
     * Documentation at https://mandrillapp.com/api/docs/messages.php.html
     *
     * @param  Email  $emails
     * @return array
     */
    protected function buildMessage(Email $email)
    {
        // Create attachments array
        $attachments = json_decode($email->getAttachments(), true);

        $to = [
            [
                'email' => $email->getRecipientEmail(),
                'name' => $email->getRecipientName(),
                'type' => 'to'
            ]
        ];

        $message = [
            'html'                => $email->getMessage(),
            'subject'             => $email->getSubject(),
            'from_email'          => $email->getSenderEmail(),
            'from_name'           => $email->getSenderName(),
            'to'                  => $to,
            'attachments'         => $attachments,
            'headers'             => json_decode($email->getHeaders(), true),
            'important'           => false,
            'track_opens'         => true,
            'track_clicks'        => true,
            'auto_text'           => true,
            'auto_html'           => false,
            'inline_css'          => true,
            'url_strip_qs'        => null,
            'preserve_recipients' => false,
            'bcc_address'         => $email->getBcc(),
            'merge'               => true,
        ];

        return $message;
    }
}
