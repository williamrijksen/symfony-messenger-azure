<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

final class Sender implements SenderInterface
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
     * @var string
     */
    private $topic;

    public function __construct(Connection $connection, SerializerInterface $serializer, string $topic)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->topic = $topic;
    }

    public function send(Envelope $envelope): Envelope
    {
        $this->connection->publish($this->topic, $this->serializer->encode($envelope));

        return $envelope;
    }
}
