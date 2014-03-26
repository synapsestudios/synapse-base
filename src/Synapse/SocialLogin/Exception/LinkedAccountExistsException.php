<?php

namespace Synapse\SocialLogin\Exception;

/**
 * Exception to be thrown whenever a conflict arises from a social login
 * account already being linked to another account.
 */
class LinkedAccountExistsException extends \RuntimeException
{
}
