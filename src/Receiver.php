<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter;

use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use WindowsAzure\ServiceBus\Models\ReceiveMessageOptions;

final class Receiver implements ReceiverInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var bool|null
     */
    private $shouldStop;

    /**
     * @var string
     */
    private $topic;

    public function __construct(Connection $connection, SerializerInterface $serializer, string $topic)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->topic = $topic;
    }

    public function receive(callable $handler): void
    {
        $options = new ReceiveMessageOptions();
        $options->setPeekLock();

        while (!$this->shouldStop) {
            if (null === $message = $this->connection->receiveSubscriptionMessage($this->topic, $options)) {
                $handler(null);

                \usleep(200000);

                continue;
            }

            if (null === $message) {
                $handler(null);
                continue;
            }

            $handler($this->serializer->decode(\json_decode((string) $message->getBody(), true)));
            $this->connection->deleteMessage($message);
        }
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }
}
