<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter\Tests\Bundle\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Messenger\Tests\Fixtures\DummyMessage;
use WilliamRijksen\AzureMessengerAdapter\Bundle\DependencyInjection\Compiler\AzureAdapterPass;

class AzureAdapterPassTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(CompilerPassInterface::class, new AzureAdapterPass());
    }

    public function testProcess(): void
    {
        $senderLocatorProphecy = $this->prophesize(Definition::class);
        $senderLocatorProphecy->replaceArgument(0, Argument::type('array'))->shouldBeCalled();
        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $containerBuilderProphecy->getParameter('azure_messenger.messages')->shouldBeCalled()->willReturn([
            DummyMessage::class => [
                'topic' => 'test',
            ],
        ]);
        $containerBuilderProphecy->setDefinition('azure_messenger.sender.test', Argument::type(Definition::class))->shouldBeCalled();
        $containerBuilderProphecy->getDefinition('messenger.senders_locator')->shouldBeCalled()->willReturn($senderLocatorProphecy->reveal());
        $containerBuilderProphecy->setDefinition('azure_messenger.receiver.test', Argument::type(Definition::class))->shouldBeCalled();
        (new AzureAdapterPass())->process($containerBuilderProphecy->reveal());
    }
}
