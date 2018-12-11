Azure PubSub adapter for symfony/messenger
===========================

[![Build Status](https://travis-ci.org/williamrijksen/symfony-messenger-azure.svg?branch=master)](https://travis-ci.org/williamrijksen/symfony-messenger-azure)

This is an experimental Receiver/Sender on Azure for the symfony/messenger component for topic and subscribers.

## Quick start

First of all: This uses topics / subscriptions like described [here](https://docs.microsoft.com/en-us/azure/service-bus-messaging/service-bus-php-how-to-use-topics-subscriptions). Make sure you have a connection-string ready.

For now we're exposing a bundle which is pre-configuring the Messenger component with receivers and senders.

```console
composer require symfony/messenger williamrijksen/symfony-messenger-azure
```

Add the bundle `new WilliamRijksen\AzureMessengerAdapter\Bundle\AzureMessengerAdapterBundle()`.

Add the following configuration:

```yaml
azure_messenger_adapter:
    azure:
        connectionString: 'Endpoint=<your token>'
        subscriptionName: 'name of subscription' #topic will be automatically created by this bundle
    messages:
        'App\Message\Foo': 'foo_topic' #topic will be automatically created by this bundle
```

Add a message handler:

```php
<?php

namespace App\MessageHandler;

use App\Message\Foo;

final class FooHandler
{
    public function __invoke(Foo $message)
    {
    }
}
```

Tag it:

```yaml
services:
  App\MessageHandler\FooHandler:
      tags:
          - { name: messenger.message_handler }
```

You're done!

Launch `bin/console messenger:consume-messages azure_messenger.receiver.foo_queue` and dispatch messages from the bus:

```php
<?php
$bus->dispatch(new Foo());
```

## Configuration reference

```yaml
azure_messenger_adapter:
    azure:
        connectionString: 'Endpoint=<your token>'
        subscriptionName: 'name of subscription'
    messages:
        'App\Message\Foo': 'foo_topic'
```

## Links
 - Inspired by [soyuka/symfony-messenger-redis](https://github.com/soyuka/symfony-messenger-redis)
 - [How to use Service Bus topics with PHP](https://docs.microsoft.com/en-us/azure/service-bus-messaging/service-bus-php-how-to-use-topics-subscriptions)
