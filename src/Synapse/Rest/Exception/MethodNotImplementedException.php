<?php

namespace Synapse\Rest\Exception;

/**
 * Exception to be thrown whenever a REST method is called but it is not implemented
 */
class MethodNotImplementedException extends \BadMethodCallException
{
}
