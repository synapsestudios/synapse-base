<?php

namespace Synapse\Mapper;

use Synapse\Stdlib\DataObject;

class PaginationData extends DataObject
{
    protected $object = [
        'page'             => 1,
        'page_count'       => null,
        'result_count'     => null,
        'results_per_page' => null,
    ];
}
