<?php

declare(strict_types=1);

namespace WilliamRijksen\AzureMessengerAdapter\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();

        /** @var ArrayNodeDefinition */
        $rootNode = $treeBuilder->root('azure_messenger');

        $rootNode->canBeDisabled()
            ->children()
                ->arrayNode('messages')
                    ->useAttributeAsKey('message')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) {
                                return ['topic' => $v];
                            })
                        ->end()
                        ->children()
                            ->scalarNode('topic')
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('azure')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('connectionString')
                        ->isRequired()
                        ->info('Azure service bus connection string')
                    ->end()
                    ->scalarNode('subscriptionName')
                        ->defaultNull()
                        ->info('Azure service bus subscription name')
                    ->end()
                ->end()
            ->end()
            ->scalarNode('cache')
                ->defaultNull()
                ->info('When the cache service is defined, it is possible to cache the subscription and topic exists checks')
            ->end()
        ->end();

        return $treeBuilder;
    }
}
