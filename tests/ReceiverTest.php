<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Tests\Fixtures\DummyMessage;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer as SerializerComponent;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use WilliamRijksen\AzureMessengerAdapter\Connection;
use WilliamRijksen\AzureMessengerAdapter\Receiver;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;

class ReceiverTest extends TestCase
{
    private $topicName = 'test-topic';

    public function testItSendTheDecodedMessageToTheHandler(): void
    {
        $serializer = new Serializer(
            new SerializerComponent\Serializer([new ObjectNormalizer()], ['json' => new JsonEncoder()])
        );
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $brokerMessage = new BrokeredMessage(\json_encode([
            'body' => '{"message": "Hi"}',
            'headers' => [
                'type' => DummyMessage::class,
            ],
        ]));
        $connection->method('receiveSubscriptionMessage')->willReturn($brokerMessage);

        $receiver = new Receiver($connection, $serializer, $this->topicName);
        $receiver->receive(function (?Envelope $envelope) use ($receiver): void {
            $this->assertEquals(new DummyMessage('Hi'), $envelope->getMessage());
            $receiver->stop();
        });
    }
}
