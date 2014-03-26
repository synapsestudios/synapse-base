<?php

namespace Synapse\SocialLogin\Exception;

/**
 * Exception to be thrown whenever a social login account is expected to be linked
 * to an account, but no linked account is found.
 */
class NoLinkedAccountException extends \RuntimeException
{
}
