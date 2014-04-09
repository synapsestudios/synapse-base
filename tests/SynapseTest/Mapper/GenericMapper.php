<?php

namespace SynapseTest\Mapper;

use Synapse\Mapper\AbstractMapper;
use Synapse\Mapper\InserterTrait;
use Synapse\Mapper\FinderTrait;
use Synapse\Mapper\UpdaterTrait;
use Synapse\Mapper\DeleterTrait;

class GenericMapper extends AbstractMapper
{
    use InserterTrait;
    use FinderTrait;
    use UpdaterTrait;
    use DeleterTrait;

    protected $tableName = 'test';
}
