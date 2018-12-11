<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter\Tests\Bundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Messenger\Tests\Fixtures\DummyMessage;
use WilliamRijksen\AzureMessengerAdapter\Bundle\DependencyInjection\AzureMessengerAdapterExtension;
use WilliamRijksen\AzureMessengerAdapter\Connection;

class AzureMessengerAdapterExtensionTest extends TestCase
{
    /**
     * @var AzureMessengerAdapterExtension
     */
    private $extension;

    public function setUp(): void
    {
        $this->extension = new AzureMessengerAdapterExtension();
    }

    public function testConstruct(): void
    {
        $this->extension = new AzureMessengerAdapterExtension();
        $this->assertInstanceOf(ExtensionInterface::class, $this->extension);
        $this->assertInstanceOf(ConfigurationExtensionInterface::class, $this->extension);
    }

    public function testLoad(): void
    {
        $config = ['azure_messenger' => [
            'messages' => [DummyMessage::class => 'topic_name'],
            'azure' => [
                'connectionString' => 'Endpoint=test-endpoint-url',
            ],
        ]];

        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $containerBuilderProphecy->setParameter('azure_messenger.messages', [DummyMessage::class => ['topic' => 'topic_name']])->shouldBeCalled();

        $self = $this;
        $containerBuilderProphecy->setDefinitions(Argument::type('array'))->will(function ($args) use ($self): void {
            $self->assertEquals(\array_keys($args[0]), [Connection::class, 'azure_messenger.servicebus_builder', 'azure_messenger.servicebus']);
        })->shouldBeCalled();
        $this->extension->load($config, $containerBuilderProphecy->reveal());
    }
}
