<?php

namespace Synapse\Email\Mapper;

use Synapse\Mapper\AbstractMapper;
use Synapse\Mapper\InserterTrait;
use Synapse\Mapper\FinderTrait;
use Synapse\Mapper\UpdaterTrait;
use Synapse\Mapper\DeleterTrait;

/**
 * Email mapper
 */
class Email extends AbstractMapper
{
    /**
     * Use all mapper traits, making this a general purpose mapper
     */
    use InserterTrait;
    use FinderTrait;
    use UpdaterTrait;
    use DeleterTrait;

    /**
     * {@inheritDoc}
     */
    protected $tableName = 'emails';
}
