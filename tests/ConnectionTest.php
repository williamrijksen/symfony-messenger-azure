<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Tests\Fixtures\DummyMessage;
use WilliamRijksen\AzureMessengerAdapter\Connection;
use WilliamRijksen\AzureMessengerAdapter\Exception\AzureMessengerException;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\ServiceBus\Internal\IServiceBus;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;
use WindowsAzure\ServiceBus\Models\ListSubscriptionsResult;
use WindowsAzure\ServiceBus\Models\ListTopicsResult;
use WindowsAzure\ServiceBus\Models\SubscriptionInfo;
use WindowsAzure\ServiceBus\Models\TopicInfo;

final class ConnectionTest extends TestCase
{
    /**
     * @var IServiceBus
     */
    private $serviceBus;

    public function setUp(): void
    {
        $this->serviceBus = $this->getMockBuilder(IServiceBus::class)->disableOriginalConstructor()->getMock();
    }

    public function testItSendsTopicMessages(): void
    {
        $this->expectNotToPerformAssertions();
        $listTopicsResult = new ListTopicsResult();
        $topicName = 'test';
        $listTopicsResult->setTopicInfos([
            new TopicInfo($topicName),
        ]);
        $message = ['body' => '...', 'headers' => ['type' => DummyMessage::class]];
        $this->serviceBus->method('listTopics')->with()->willReturn($listTopicsResult);
        $this->serviceBus->method('sendTopicMessage')->with($topicName, new BrokeredMessage(\json_encode($message)))->willReturn($listTopicsResult);

        $connection = new Connection(
            $this->serviceBus,
            'subscriptionName'
        );
        $connection->publish($topicName, $message);
    }

    public function testThrowsExceptionWhenListTopicsFailedWhenPublishing(): void
    {
        $this->expectException(AzureMessengerException::class);
        $this->serviceBus->method('listTopics')->willThrowException(new ServiceException('test'));

        $connection = new Connection(
            $this->serviceBus,
            'subscriptionName'
        );
        $encoded = ['body' => '...', 'headers' => ['type' => DummyMessage::class]];
        $connection->publish('test', $encoded);
    }

    public function testCreatesTopicFirstWhenNotExistsWhenPublishing(): void
    {
        $this->expectNotToPerformAssertions();
        $listTopicsResult = new ListTopicsResult();
        $listTopicsResult->setTopicInfos([
            new TopicInfo('test-not-exists'),
        ]);
        $this->serviceBus->method('listTopics')->willReturn($listTopicsResult);
        $this->serviceBus->method('createTopic')->with(new TopicInfo('test'));
        $this->serviceBus->method('sendTopicMessage')->with('test');

        $connection = new Connection(
            $this->serviceBus,
            'subscriptionName'
        );
        $encoded = ['body' => '...', 'headers' => ['type' => DummyMessage::class]];
        $connection->publish('test', $encoded);
    }

    public function testItReceivesSubscriptionMessage(): void
    {
        $this->expectNotToPerformAssertions();
        $listSubscriptionsResult = new ListSubscriptionsResult();
        $topicName = 'test';
        $listSubscriptionsResult->setSubscriptionInfos([
            new SubscriptionInfo($topicName),
        ]);
        $message = ['body' => '...', 'headers' => ['type' => DummyMessage::class]];
        $this->serviceBus->method('listSubscriptions')->with($topicName)->willReturn($listSubscriptionsResult);
        $this->serviceBus->method('receiveSubscriptionMessage')->with($topicName, 'subscriptionName', null)->willReturn(new BrokeredMessage(
            \json_encode($message)
        ));

        $connection = new Connection(
            $this->serviceBus,
            'subscriptionName'
        );
        $connection->receiveSubscriptionMessage($topicName);
    }

    public function testThrowsExceptionWhenListSubscriptionFailedWhenPublishing(): void
    {
        $topicName = 'test';
        $listSubscriptionsResult = new ListSubscriptionsResult();
        $listSubscriptionsResult->setSubscriptionInfos([]);
        $this->expectException(AzureMessengerException::class);
        $this->serviceBus->method('listSubscriptions')->with($topicName)->willReturn($listSubscriptionsResult);
        $this->serviceBus->method('receiveSubscriptionMessage')->with($topicName, 'subscriptionName', null)->willThrowException(new ServiceException('test'));

        $connection = new Connection(
            $this->serviceBus,
            'subscriptionName'
        );
        $connection->receiveSubscriptionMessage($topicName);
    }

    public function testCreatesSubscriptionFirstWhenNotExistsWhenReceiving(): void
    {
        $this->expectNotToPerformAssertions();
        $message = ['body' => '...', 'headers' => ['type' => DummyMessage::class]];
        $listSubscriptionsResult = new ListSubscriptionsResult();
        $topicName = 'test';
        $listSubscriptionsResult->setSubscriptionInfos([]);
        $this->serviceBus->method('createSubscription')->with($topicName, new SubscriptionInfo('subscriptionName'));
        $this->serviceBus->method('listSubscriptions')->with($topicName)->willReturn($listSubscriptionsResult);
        $this->serviceBus->method('receiveSubscriptionMessage')->with($topicName, 'subscriptionName', null)->willReturn(new BrokeredMessage(
            \json_encode($message)
        ));

        $connection = new Connection(
            $this->serviceBus,
            'subscriptionName'
        );
        $connection->receiveSubscriptionMessage($topicName);
    }
}
