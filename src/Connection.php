<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter;

use WilliamRijksen\AzureMessengerAdapter\Exception\AzureMessengerException;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\ServiceBus\Internal\IServiceBus;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;
use WindowsAzure\ServiceBus\Models\ReceiveMessageOptions;
use WindowsAzure\ServiceBus\Models\SubscriptionInfo;
use WindowsAzure\ServiceBus\Models\TopicInfo;

class Connection
{
    /**
     * @var IServiceBus
     */
    private $serviceBus;

    /**
     * @var string
     */
    private $subscriptionName;

    public function __construct(IServiceBus $serviceBus, string $subscriptionName = 'AllMessages')
    {
        $this->serviceBus = $serviceBus;
        $this->subscriptionName = $subscriptionName;
    }

    public function publish(string $topicName, array $message): void
    {
        $this->createTopic($topicName);

        try {
            $body = \json_encode($message);
            if (false === $body) {
                throw AzureMessengerException::whenSendingTopicMessage($topicName);
            }

            $this->serviceBus->sendTopicMessage(
                $topicName,
                new BrokeredMessage($body)
            );
        } catch (ServiceException $e) {
            throw AzureMessengerException::whenSendingTopicMessage($topicName, $e);
        }
    }

    private function createTopic(string $topicName): void
    {
        if ($this->checkTopicExists($topicName)) {
            return;
        }

        try {
            $this->serviceBus->createTopic(new TopicInfo($topicName));
        } catch (ServiceException $e) {
            throw AzureMessengerException::whenCreatingTopic($topicName, $e);
        }
    }

    private function checkTopicExists(string $topicName): bool
    {
        try {
            $topics = $this->serviceBus->listTopics();
        } catch (ServiceException $e) {
            throw AzureMessengerException::whenListingTopics($e);
        }

        /*
         * @var TopicInfo
         */
        foreach ($topics->getTopicInfos() as $topic) {
            if ($topic->getTitle() === $topicName) {
                return true;
            }
        }

        return false;
    }

    public function receiveSubscriptionMessage(string $topicName, ReceiveMessageOptions $receiveMessageOptions = null): ?BrokeredMessage
    {
        $this->createSubscription($topicName);

        try {
            return $this->serviceBus->receiveSubscriptionMessage($topicName, $this->subscriptionName, $receiveMessageOptions);
        } catch (ServiceException $e) {
            throw AzureMessengerException::whenReceivingMessages($e);
        }
    }

    private function createSubscription(string $topicName): void
    {
        if ($this->checkSubscriptionExists($topicName)) {
            return;
        }

        try {
            $this->serviceBus->createSubscription($topicName, new SubscriptionInfo($this->subscriptionName));
        } catch (ServiceException $e) {
            throw AzureMessengerException::whenCreatingSubscription($topicName, $this->subscriptionName, $e);
        }
    }

    private function checkSubscriptionExists(string $topicName): bool
    {
        try {
            $subscriptions = $this->serviceBus->listSubscriptions($topicName);
        } catch (ServiceException $e) {
            throw AzureMessengerException::whenListingSubscriptions($e);
        }

        /*
         * @var SubscriptionInfo
         */
        foreach ($subscriptions->getSubscriptionInfos() as $subscription) {
            if ($subscription->getTitle() === $this->subscriptionName) {
                return true;
            }
        }

        return false;
    }

    public function deleteMessage(BrokeredMessage $message): void
    {
        try {
            $this->serviceBus->deleteMessage($message);
        } catch (ServiceException $e) {
            throw AzureMessengerException::whenDeletingMessage($message, $e);
        }
    }
}
