<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter\Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use WilliamRijksen\AzureMessengerAdapter\Connection;
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\ServiceBus\Internal\IServiceBus;

final class AzureMessengerAdapterExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        if (!$config['enabled']) {
            return;
        }

        $container->setParameter('azure_messenger.messages', $config['messages']);
        $connectionString = $config['azure']['connectionString'];

        $serviceBusBuilderDefinition = new Definition(ServicesBuilder::class);
        $serviceBusBuilderDefinition->setFactory(ServicesBuilder::class.'::getInstance');

        $serviceBusDefinition = new Definition(
            IServiceBus::class
        );
        $serviceBusDefinition->setFactory([new Reference('azure_messenger.servicebus_builder'), 'createServiceBusService']);
        $serviceBusDefinition->addArgument($connectionString);

        $connectionDefinition = new Definition(Connection::class, [
            $serviceBusDefinition,
            $config['azure']['subscriptionName'],
            $config['cache'] ? new Definition(new Reference($config['cache'])) : null,
        ]);

        $container->setDefinitions([
            Connection::class => $connectionDefinition,
            'azure_messenger.servicebus_builder' => $serviceBusBuilderDefinition,
            'azure_messenger.servicebus' => $serviceBusDefinition,
        ]);
    }
}
