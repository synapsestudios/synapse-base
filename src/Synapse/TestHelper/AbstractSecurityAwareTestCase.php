<?php

namespace Synapse\TestHelper;

use PHPUnit_Framework_TestCase;

/**
 * Extend this class to create mocks of the security token and context for testing
 * @deprecated - use SecurityContextMockInjector instead
 */
abstract class AbstractSecurityAwareTestCase extends PHPUnit_Framework_TestCase
{
    use SecurityContextMockInjector;

    /**
     * @deprecated - use the static $loggedInUserId in SecurityContextMockInjector
     */
    const LOGGED_IN_USER_ID = 42;
}
