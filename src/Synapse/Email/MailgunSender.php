<?php

namespace Synapse\Email;

use Mailgun;

/**
 * Service to send emails
 */
class MailgunSender extends AbstractSender
{
    /**
     * @var string
     */
    protected $domain;

    /**
     * @var Mailgun
     */
    protected $mailgun;

    /**
     * @var EmailMapper
     */
    protected $mapper;

    /**
     * @param string      $domain
     * @param Mailgun     $mailgun
     * @param EmailMapper $mapper
     */
    public function __construct($domain, Mailgun $mailgun, EmailMapper $mapper)
    {
        $this->domain  = $domain;
        $this->mailgun = $mailgun;
        $this->mapper  = $mapper;
    }

    /**
     * {@inheritDoc}
     */
    public function send(EmailEntity $email)
    {
        $time = time();

        $message = $this->buildMessage($email);

        $result = $this->mailgun->sendMessage($this->domain, $message);
        $result = array_shift($result);

        $email->setStatus($result['status']);
        $email->setSent($time);
        $email->setUpdated($time);

        $this->mapper->update($email);

        return [$email, $result];
    }

    /**
     * Build Mailgun compatible message array from email entity
     *
     * Documentation at https://mandrillapp.com/api/docs/messages.php.html
     *
     * @param  EmailEntity  $emails
     * @return array
     */
    protected function buildMessage(EmailEntity $email)
    {
        // Create attachments array
        $attachments = json_decode($email->getAttachments(), true);

        // Convert Mandrill format to Mailgun format
        $attachments = array_map(
            function ($attachment) {
                if (isset($attachment['content'])) {
                    $attachment['data'] = base64_decode(
                        Arr::remove($attachment, 'content')
                    );
                }
                if (isset($attachment['name'])) {
                    $attachment['filename'] = Arr::remove($attachment, 'name');
                }
                if (isset($attachment['type'])) {
                    $attachment['contentType'] = Arr::remove($attachment, 'type');
                }
            },
            $attachments
        );

        if ($email->getSenderName()) {
            $from = "${$email->getSenderName()} <${$email->getSenderEmail()}>";
        } else {
            $from = $email->getSenderEmail();
        }

        $message = [
            'html'        => $email->getMessage(),
            'subject'     => $email->getSubject(),
            'from'        => $from,
            'to'          => $this->filterThroughWhitelist($email->getRecipientEmail()),
            'attachments' => $attachments,
        ];

        return $message;
    }
}
