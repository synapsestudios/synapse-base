<?php

namespace Test\Synapse\Email;

use PHPUnit_Framework_TestCase;
use Synapse\Email\MandrillSender;
use Synapse\Email\EmailEntity;
use stdClass;

class MandrillSenderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->captured = new stdClass();

        $this->setUpMockMandrill();
        $this->setUpMockEmailMapper();

        $this->sender = new MandrillSender(
            $this->mockMandrill,
            $this->mockEmailMapper
        );

        $this->sender->setConfig($this->getTestConfig());
    }

    public function setUpMockMandrill()
    {
        $this->mockMandrill = $this->getMockBuilder('Mandrill')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockMandrillMessages = $this->getMockBuilder('Mandrill_Messages')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockMandrill->messages = $this->mockMandrillMessages;
    }

    public function setUpMockEmailMapper()
    {
        $this->mockEmailMapper = $this->getMockBuilder('Synapse\Email\EmailMapper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function createEmailEntity($sender = '', $recipient = '')
    {
        return new EmailEntity([
            'sender_email'    => $sender,
            'recipient_email' => $recipient,
        ]);
    }

    public function captureEmailAddresses()
    {
        $this->mockMandrillMessages->expects($this->any())
            ->method('send')
            ->will($this->returnCallback(function($data) {
                $this->captured->recipientEmail = $data['to'][0]['email'];
                $this->captured->senderEmail    = $data['from_email'];

                return [['status' => 1]];
            }));
    }

    public function getTestConfig()
    {
        return [
            'whitelist' => [
                'whitelist' => ['foo@bar.com', 'domain.com', 'test.com'],
                'trap'      => 'trap@example.com',
            ],
        ];
    }

    public function provideSendersAndRecipientsWithExpectedFilteredEmails()
    {
        return [
            ['foo@bar.com', 'foo@bar.com', 'joe@domain.com', 'joe@domain.com'],
            ['foo@bar.com', 'foo@bar.com', 'joe@invalid.com', 'trap+joe+invalid.com@example.com'],
            ['joe@invalid.com', 'trap+joe+invalid.com@example.com', 'foo@bar.com', 'foo@bar.com'],
            ['one@two.com', 'trap+one+two.com@example.com', 'three@four.com', 'trap+three+four.com@example.com'],
        ];
    }

    /**
     * @dataProvider provideSendersAndRecipientsWithExpectedFilteredEmails
     */
    public function testSendRunsEmailAddressesThroughWhitelistFilter($sender, $expectedSender, $recipient, $expectedRecipient)
    {
        $this->captureEmailAddresses();

        $email = $this->createEmailEntity();

        $email->setSenderEmail($sender);
        $email->setRecipientEmail($recipient);

        $this->sender->send($email);

        $this->assertEquals(
            $expectedSender,
            $this->captured->senderEmail
        );

        $this->assertEquals(
            $expectedRecipient,
            $this->captured->recipientEmail
        );
    }
}
