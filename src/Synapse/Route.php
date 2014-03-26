<?php

namespace Synapse;

use Silex\Route as SilexRoute;
use Silex\Route\SecurityTrait;

/**
 * Silex route with additional traits
 */
class Route extends SilexRoute
{
    use SecurityTrait;
}
