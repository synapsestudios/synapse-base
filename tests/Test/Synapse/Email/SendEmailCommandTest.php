<?php

namespace Test\Synapse\Email;

use PHPUnit_Framework_TestCase;
use Synapse\Email\SendEmailCommand;
use Synapse\Command\Install\Generate;
use Synapse\Email\EmailEntity;

class SendEmailCommandTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sendCommand = new SendEmailCommand('email:send');

        // Create mocks
        $this->mockEmailMapper = $this->getMockBuilder('Synapse\Email\EmailMapper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockEmailSender = $this->getMock('Synapse\Email\SenderInterface');
        $this->mockInputInterface = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $this->mockOutputInterface = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
    }

    public function withConfiguredSendObject()
    {
        $this->sendCommand->setEmailMapper($this->mockEmailMapper);
        $this->sendCommand->setEmailSender($this->mockEmailSender);
    }

    public function withEmailThatIsNotFound()
    {
         $this->mockInputInterface->expects($this->once())
            ->method('getArgument')
            ->with($this->equalTo('id'))
            ->will($this->returnValue('emailId'));

        $this->mockEmailMapper->expects($this->once())
            ->method('findById')
            ->with($this->equalTo('emailId'))
            ->will($this->returnValue(new EmailEntity));
    }

    public function withEmailThatIsFound()
    {
         $this->mockInputInterface->expects($this->once())
            ->method('getArgument')
            ->with($this->equalTo('id'))
            ->will($this->returnValue('emailId'));

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
        $this->sendCommand->setEmailMapper($this->mockEmailMapper);

        $this->sendCommand->run(
            $this->mockInputInterface,
            $this->mockOutputInterface
        );
    }

    /**
     * @expectedException LogicException
     */
    public function testThrowsExceptionIfEmailMapperNotSet()
    {
        $this->sendCommand->setEmailSender($this->mockEmailSender);

        $this->sendCommand->run(
            $this->mockInputInterface,
            $this->mockOutputInterface
        );
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testThrowsExceptionIfEmailSpecifiedInInputNotFound()
    {
        $this->withConfiguredSendObject();
        $this->withEmailThatIsNotFound();

        $this->sendCommand->run(
            $this->mockInputInterface,
            $this->mockOutputInterface
        );
    }

    public function testEmailPassedToSendersSendMethodIfEmailFound()
    {
        $this->withConfiguredSendObject();
        $email = $this->withEmailThatIsFound();

        $this->withSuccessfullySentEmail($email);

        $this->sendCommand->run(
            $this->mockInputInterface,
            $this->mockOutputInterface
        );
    }

    public function testReturns500IfStatusIsNotSent()
    {
        $this->withConfiguredSendObject();
        $email = $this->withEmailThatIsFound();

        $this->withRejectedEmail($email);

        $returnValue = $this->sendCommand->run(
            $this->mockInputInterface,
            $this->mockOutputInterface
        );

        $this->assertEquals(500, $returnValue);
    }
}
