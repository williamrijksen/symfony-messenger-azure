<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter\Exception;

use WindowsAzure\Common\ServiceException;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;

final class AzureMessengerException extends \RuntimeException
{
    public static function whenReceivingMessages(ServiceException $e): self
    {
        return new self('An error occured when receiving messages from the servicebus', 0, $e);
    }

    public static function whenListingTopics(ServiceException $e): self
    {
        return new self('An error occured when listing topics from the servicebus', 0, $e);
    }

    public static function whenCreatingTopic(string $topicName, ServiceException $e): self
    {
        return new self(\sprintf('An error occured when creating topic "%s" at the servicebus', $topicName), 0, $e);
    }

    public static function whenSendingTopicMessage(string $topicName, ServiceException $e = null): self
    {
        return new self(\sprintf('An error occured when sending topic message "%s" tothe servicebus', $topicName), 0, $e);
    }

    public static function whenListingSubscriptions(ServiceException $e): self
    {
        return new self('An error occured when listing subscriptions from the servicebus', 0, $e);
    }

    public static function whenCreatingSubscription(string $topicName, string $subscriptionName, ServiceException $e): self
    {
        return new self(\sprintf('An error occured when creating subscription "%s" for topic "%s" at the servicebus', $subscriptionName, $topicName), 0, $e);
    }

    public static function whenDeletingMessage(BrokeredMessage $message, ServiceException $e): self
    {
        return new self(\sprintf('An error occured when deleting message "%s" from the servicebus', $message->getMessageId()), 0, $e);
    }
}
