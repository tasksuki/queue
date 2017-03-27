<?php

namespace Tasksuki\Component\Queue\Test;

use Tasksuki\Component\Driver\DriverInterface;
use Tasksuki\Component\Handler\HandlerInterface;
use Tasksuki\Component\Message\Message;
use Tasksuki\Component\Queue\Queue;
use PHPUnit\Framework\TestCase;
use Tasksuki\Component\Serializer\SerializerInterface;

class QueueTest extends TestCase
{

    public function testProduceMessage()
    {
        $message = new Message();
        $messageSerialized = 'foo_bar';

        $driver = $this->getMockBuilder(DriverInterface::class)
            ->setMethods(['send'])
            ->getMockForAbstractClass();

        $driver->expects($this->once())
            ->method('send')
            ->with('foo', $messageSerialized);

        $serializer = $this->getMockBuilder(SerializerInterface::class)
            ->setMethods(['serialize'])
            ->getMockForAbstractClass();

        $serializer->expects($this->once())
            ->method('serialize')
            ->with($message)
            ->willReturn($messageSerialized);

        $queue = new Queue('foo', $driver, $serializer);

        $queue->addMessage($message);
    }

    public function testConsumeMessage()
    {
        $message = new Message();
        $messageSerialized = 'foo_bar';

        $driver = $this->getMockBuilder(DriverInterface::class)
            ->setMethods(['receive'])
            ->getMockForAbstractClass();

        $driver->expects($this->once())
            ->method('receive')
            ->will(
                $this->returnCallback(
                    function (string $name, callable $callback) use ($messageSerialized) {
                        $this->assertEquals('foo', $name);

                        $callback($messageSerialized);
                    }
                )
            );

        $serializer = $this->getMockBuilder(SerializerInterface::class)
            ->setMethods(['unserialize'])
            ->getMockForAbstractClass();

        $serializer->expects($this->once())
            ->method('unserialize')
            ->with($messageSerialized)
            ->willReturn($message);

        $handler = $this->getMockBuilder(HandlerInterface::class)
            ->setMethods(['handle'])
            ->getMock();

        $handler->expects($this->once())
            ->method('handle')
            ->with($message);

        $queue = new Queue('foo', $driver, $serializer);
        $queue->setHandler($handler);

        $queue->checkQueue();
    }
}
