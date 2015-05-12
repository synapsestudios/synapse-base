<?php

namespace Test\Synapse\Email;

use PHPUnit_Framework_TestCase;

class AbstractSenderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sender = new Sender();

        $this->sender->setConfig($this->getTestConfig());
    }

    public function getTestConfig()
    {
        return [
            'whitelist' => [
                'list' => ['foo@bar.com', 'domain.com', 'test.com'],
                'trap' => 'trap@example.com',
            ],
        ];
    }

    /**
     * A test configuration with no valid emails but no trap set
     *
     * @return array
     */
    public function getMalconfiguredTestConfig()
    {
        return [
            'whitelist' => [
                'list' => [],
                'trap' => null,
            ],
        ];
    }

    public function provideWhitelistedAddresses()
    {
        return [
            ['foo@bar.com'],
            ['foo@domain.com'],
            ['foo@test.com'],
            ['foo123456789@test.com'],
            ['f.o+o,o@test.com'],
        ];
    }

    public function provideNonWhitelistedAddresses()
    {
        return [
            ['invalid@bar.com', 'trap+invalid+bar.com@example.com'],
            ['foo@123.com', 'trap+foo+123.com@example.com'],
            ['test@bar.com', 'trap+test+bar.com@example.com'],
        ];
    }

    /**
     * @dataProvider provideWhitelistedAddresses
     */
    public function testFilterThroughWhitelistDoesNotModifyWhitelistedAddressesOrDomains($emailAddress)
    {
        $this->assertEquals(
            $emailAddress,
            $this->sender->getFilteredEmailAddress($emailAddress)
        );
    }

    /**
     * @dataProvider provideNonWhitelistedAddresses
     */
    public function testFilterThroughWhitelistInjectsAddressIntoTrapAddressIfNotOnWhitelist($emailAddress, $expectedFilteredAddress)
    {
        $this->assertEquals(
            $expectedFilteredAddress,
            $this->sender->getFilteredEmailAddress($emailAddress)
        );
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFilterThroughWhitelistThrowsExceptionTryingToSendToTrapAddressButNoneSet()
    {
        $this->sender->setConfig($this->getMalconfiguredTestConfig());
        $this->sender->getFilteredEmailAddress('foo@bar.com');
    }
}
