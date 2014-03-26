<?php

namespace Synapse\Email\Entity;

use Synapse\Entity\AbstractEntity;

/**
 * Email entity
 */
class Email extends AbstractEntity
{
    /**
     * Possible values for the status field
     */
    const STATUS_PENDING  = 'pending';
    const STATUS_QUEUED   = 'queued';
    const STATUS_SENT     = 'sent';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ERROR    = 'error';
    const STATUS_UNKNOWN  = 'unknown';

    /**
     * {@inheritDoc}
     */
    protected $object = [
        'id'              => null,
        'hash'            => null,
        'status'          => null,
        'subject'         => null,
        'recipient_email' => null,
        'recipient_name'  => null,
        'sender_email'    => null,
        'sender_name'     => null,
        'template_name'   => null,
        'template_data'   => null,
        'message'         => null,
        'bcc'             => null,
        'attachments'     => null,
        'headers'         => null,
        'date_sent'       => null,
        'date_created'    => null,
        'date_updated'    => null,
    ];

    /**
     * {@inheritDoc}
     */
    public function fromArray(array $values)
    {
        $entity = parent::fromArray($values);

        $entity->setDateCreated(time());
        $entity->setStatus(self::STATUS_PENDING);

        return $entity;
    }
}
