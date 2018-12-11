<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use WilliamRijksen\AzureMessengerAdapter\Connection;
use WilliamRijksen\AzureMessengerAdapter\Receiver;
use WilliamRijksen\AzureMessengerAdapter\Sender;

final class AzureAdapterPass implements CompilerPassInterface
{
    private const PREFIX = 'azure_messenger';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $azure = new Reference(Connection::class);
        // extracted from registerMessengerConfiguration
        $messageToSendersMapping = [];
        $messages = $container->getParameter(self::PREFIX.'.messages');

        if (!$messages) {
            return;
        }

        foreach ($messages as $class => $message) {
            $senderDefinition = new Definition(Sender::class, [
                $azure,
                new Reference('messenger.transport.symfony_serializer'),
                $message['topic'],
            ]);
            $senderDefinition->addTag('messenger.sender');
            $sender = self::PREFIX.'.sender.'.$message['topic'];
            $container->setDefinition($sender, $senderDefinition);
            $messageToSendersMapping[$class] = [new Reference($sender)];
        }

        $container->getDefinition('messenger.senders_locator')
            ->replaceArgument(0, $messageToSendersMapping)
        ;

        foreach ($messages as $message) {
            $receiverDefinition = new Definition(Receiver::class, [
                $azure,
                new Reference('messenger.transport.symfony_serializer'),
                $message['topic'],
            ]);
            $receiverDefinition->addTag('messenger.receiver');
            $receiver = self::PREFIX.'.receiver.'.$message['topic'];
            $container->setDefinition($receiver, $receiverDefinition);
        }
    }
}
