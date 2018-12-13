<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter;

use Psr\SimpleCache\CacheInterface;
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

    /**
     * @var CacheInterface|null
     */
    private $cache;

    public function __construct(IServiceBus $serviceBus, string $subscriptionName = 'AllMessages', ?CacheInterface $cache = null)
    {
        $this->serviceBus = $serviceBus;
        $this->subscriptionName = $subscriptionName;
        $this->cache = $cache;
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
            if ($this->cache) {
                $this->cache->set('topic_'.$topicName, true, 3600);
            }

            return;
        }

        try {
            $this->serviceBus->createTopic(new TopicInfo($topicName));
            if ($this->cache) {
                $this->cache->set('topic_'.$topicName, true, 3600);
            }
        } catch (ServiceException $e) {
            throw AzureMessengerException::whenCreatingTopic($topicName, $e);
        }
    }

    private function checkTopicExists(string $topicName): bool
    {
        if ($this->cache && true === $this->cache->get('topic_'.$topicName)) {
            return true;
        }

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
            if ($this->cache) {
                $this->cache->set('subscription_'.$topicName.'_'.$this->subscriptionName, true, 3600);
            }

            return;
        }

        try {
            // topic required for subscriptions
            $this->createTopic($topicName);
            $this->serviceBus->createSubscription($topicName, new SubscriptionInfo($this->subscriptionName));
            if ($this->cache) {
                $this->cache->set('subscription_'.$topicName.'_'.$this->subscriptionName, true, 3600);
            }
        } catch (ServiceException $e) {
            throw AzureMessengerException::whenCreatingSubscription($topicName, $this->subscriptionName, $e);
        }
    }

    private function checkSubscriptionExists(string $topicName): bool
    {
        if ($this->cache && true === $this->cache->get('subscription_'.$topicName.'_'.$this->subscriptionName)) {
            return true;
        }

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
