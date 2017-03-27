<?php

namespace Tasksuki\Component\Queue;

use Tasksuki\Component\Message\Message;
use Tasksuki\Component\Driver\DriverInterface;
use Tasksuki\Component\Handler\NullHandler;
use Tasksuki\Component\Handler\HandlerInterface;
use Tasksuki\Component\Serializer\SerializerInterface;

/**
 * Class Queue
 *
 * @package Tasksuki\Component\Queue
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Queue
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var HandlerInterface
     */
    private $handler;

    public function __construct(
        string $name,
        DriverInterface $driver,
        SerializerInterface $serializer,
        HandlerInterface $handler = null
    ) {
        $this->name = $name;
        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->handler = $handler ?? new NullHandler();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return DriverInterface
     */
    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * @return HandlerInterface
     */
    public function getHandler(): HandlerInterface
    {
        return $this->handler;
    }

    /**
     * @param HandlerInterface $handler
     *
     * @return Queue
     */
    public function setHandler(HandlerInterface $handler): Queue
    {
        $this->handler = $handler;

        return $this;
    }

    public function addMessage(Message $message)
    {
        $data = $this->getSerializer()->serialize($message);

        $this->getDriver()->send($this->getName(), $data);

        return $message;
    }

    public function checkQueue()
    {
        $callback = function (string $data) {
            $message = $this->getSerializer()->unserialize($data);

            $this->getHandler()->handle($message);
        };

        $this->getDriver()->receive($this->getName(), $callback);
    }
}