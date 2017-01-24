<?php

namespace Synapse\Email;

use Mailgun\Mailgun;
use Synapse\Template\TemplateService;

/**
 * Service to send emails
 */
class MailgunSender extends AbstractSender
{
    /**
     * @var Mailgun
     */
    protected $mailgun;

    /**
     * @var EmailMapper
     */
    protected $mapper;

    /**
     * @var TemplateService
     */
    protected $templateService;

    /**
     * @param string      $domain
     * @param Mailgun     $mailgun
     * @param EmailMapper $mapper
     */
    public function __construct(
        Mailgun $mailgun,
        EmailMapper $mapper,
        TemplateService $templateService
    ) {
        $this->mailgun = $mailgun;
        $this->mapper  = $mapper;
        $this->templateService = $templateService;
    }

    /**
     * {@inheritDoc}
     */
    public function send(EmailEntity $email)
    {
        $time = time();

        $message = $this->buildMessage($email);

        // Get domain from the "from" address
        if (!preg_match('/@(.+)$/', $email->getSenderEmail(), $matches)) {
            throw new \Exception("Invalid from address: {$email->getSenderEmail()}");
        }
        $domain = Arr::get($this->config, 'sender_domain', $matches[1]);

        $result = $this->mailgun->sendMessage($domain, $message);

        $email->setStatus($result->http_response_code === 200 ? 'sent' : 'error');
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
        $attachments = json_decode($email->getAttachments(), true) ?: [];

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

                return attachment;
            },
            $attachments
        );

        if ($email->getSenderName()) {
            $from = "{$email->getSenderName()} <{$email->getSenderEmail()}>";
        } else {
            $from = $email->getSenderEmail();
        }

        if ($email->getTemplateName()) {
            $html = $this->templateService->renderHbsForEmail(
                $email->getTemplateName(),
                $email->getTemplateData() ? json_decode($email->getTemplateData(), true) : []
            );
        } else {
            $html = $email->getMessage();
        }

        $message = [
            'html'        => $html,
            'subject'     => $email->getSubject(),
            'from'        => $from,
            'to'          => $this->filterThroughWhitelist($email->getRecipientEmail()),
            'attachments' => $attachments,
        ];

        if ($email->getHeaders()) {
            $headers = [];
            foreach (json_decode($email->getHeaders(), true) as $key => $value) {
                $headers["h:{$key}"] = $value;
            }
            $message['headers'] = $headers;
        }

        return $message;
    }
}
