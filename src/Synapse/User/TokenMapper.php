<?php

namespace Synapse\User;

use Synapse\Mapper;

class TokenMapper extends Mapper\AbstractMapper
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
