<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter\Tests\Bundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Messenger\Tests\Fixtures\DummyMessage;
use WilliamRijksen\AzureMessengerAdapter\Bundle\DependencyInjection\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Processor
     */
    private $processor;

    public function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testDefaultConfig(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();
        $config = $this->processor->processConfiguration($this->configuration, [
            'azure_messenger' => [
                'messages' => [
                    DummyMessage::class => 'topic_name',
                ],
                'azure' => [
                    'connectionString' => 'Endpoint',
                ],
            ],
        ]);

        $this->assertInstanceOf(ConfigurationInterface::class, $this->configuration);
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
        $this->assertEquals([
            'enabled' => true,
            'messages' => [DummyMessage::class => ['topic' => 'topic_name']],
            'azure' => ['connectionString' => 'Endpoint', 'subscriptionName' => null],
        ], $config);
    }
}
