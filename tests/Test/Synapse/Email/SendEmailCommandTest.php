<?php

namespace Test\Synapse\Email;

use Synapse\TestHelper\CommandTestCase;
use Synapse\Email\SendEmailCommandProxy;
use Synapse\Email\SendEmailCommand;
use Synapse\Install\GenerateInstallCommand;
use Synapse\Email\EmailEntity;

class SendEmailCommandTest extends CommandTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->command = new SendEmailCommandProxy('email:send');
        $this->command->setApp($this->getMockApp());

        // Create mocks
        $this->mockEmailMapper = $this->getMockBuilder('Synapse\Email\EmailMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockEmailSender = $this->getMock('Synapse\Email\SenderInterface');

        $this->mockInputInterface = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $this->mockOutputInterface = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
    }

    public function getMockApp()
    {
        return $this->getMockBuilder('Synapse\Application')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function withConfiguredSendObject()
    {
        $this->command->setFactory(function () {
            $command = new SendEmailCommand;
            $command->setEmailMapper($this->mockEmailMapper);
            $command->setEmailSender($this->mockEmailSender);
            return $command;
        });
    }

    public function withEmailNotFound()
    {
        $this->mockEmailMapper->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(false));
    }

    public function withEmailFound()
    {
        $email = new EmailEntity();
        $email->exchangeArray(['id' => 'emailId']);

        $this->mockEmailMapper->expects($this->once())
            ->method('findById')
            ->with($this->equalTo('emailId'))
            ->will($this->returnValue($email));

        return $email;
    }

    public function withSuccessfullySentEmail($email)
    {
        $email->setStatus(EmailEntity::STATUS_SENT);

        $this->mockEmailSender->expects($this->once())
            ->method('send')
            ->with($this->equalTo($email))
            ->will($this->returnValue([
                $email,
                [
                    'status' => 'ok'
                ]
            ]));
    }

    public function withRejectedEmail($email)
    {
        $email->setStatus(EmailEntity::STATUS_REJECTED);

        $this->mockEmailSender->expects($this->once())
            ->method('send')
            ->with($this->equalTo($email))
            ->will($this->returnValue([
                $email,
                [
                    'status'        => 'not ok',
                    'reject_reason' => 'reason'
                ]
            ]));
    }

    /**
     * @expectedException LogicException
     */
    public function testThrowsExceptionIfEmailSenderNotSet()
    {
        $this->command->setFactory(function () {
            $command = new SendEmailCommand;
            $command->setEmailMapper($this->mockEmailMapper);
            return $command;
        });

        $this->executeCommand();
    }

    /**
     * @expectedException LogicException
     */
    public function testThrowsExceptionIfEmailMapperNotSet()
    {
        $this->command->setFactory(function () {
            $command = new SendEmailCommand;
            $command->setEmailSender($this->mockEmailSender);
            return $command;
        });

        $this->executeCommand();
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testThrowsExceptionIfEmailSpecifiedInInputNotFound()
    {
        $this->withConfiguredSendObject();
        $this->withEmailNotFound();

        $this->executeCommand(['id' => '1']);
    }

    public function testEmailPassedToSendersSendMethodIfEmailFound()
    {
        $this->withConfiguredSendObject();
        $email = $this->withEmailFound();

        $this->withSuccessfullySentEmail($email);

        $this->executeCommand(['id' => 'emailId']);
    }

    public function testReturns500IfStatusIsNotSent()
    {
        $this->withConfiguredSendObject();
        $email = $this->withEmailFound();

        $this->withRejectedEmail($email);

        $returnValue = $this->executeCommand(['id' => 'emailId']);

        $this->assertEquals(500, $returnValue);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProxyThrowsExceptionIfFactoryDoesNotReturnCommand()
    {
        $this->command->setFactory(function () {
            return null;
        });

        $this->executeCommand();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testProxyThrowsExceptionIfFactoryReturnsIncorrectInstance()
    {
        $this->command->setFactory(function () {
            return new GenerateInstallCommand;
        });

        $this->executeCommand();
    }
}
