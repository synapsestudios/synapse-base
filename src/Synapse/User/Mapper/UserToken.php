<?php

namespace Synapse\User\Mapper;

use Synapse\Mapper;

class UserToken extends Mapper\AbstractMapper
{
    use Mapper\InserterTrait;
    use Mapper\FinderTrait;
    use Mapper\UpdaterTrait;
    use Mapper\DeleterTrait;

    /**
     * {@inheritDoc}
     */
    protected $tableName = 'user_tokens';
}
