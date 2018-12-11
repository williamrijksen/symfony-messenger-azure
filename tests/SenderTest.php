<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Tests\Fixtures\DummyMessage;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use WilliamRijksen\AzureMessengerAdapter\Connection;
use WilliamRijksen\AzureMessengerAdapter\Sender;

class SenderTest extends TestCase
{
    public function testItSendsTheEncodedMessage(): void
    {
        $envelope = new Envelope(new DummyMessage('Oy'));
        $encoded = ['body' => '...', 'headers' => ['type' => DummyMessage::class]];
        $serializer = $this->getMockBuilder(SerializerInterface::class)->getMock();
        $serializer->method('encode')->with($envelope)->willReturnOnConsecutiveCalls($encoded);
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('publish')->with('topic', $encoded);

        $sender = new Sender($connection, $serializer, 'topic');
        $sender->send($envelope);
    }
}
