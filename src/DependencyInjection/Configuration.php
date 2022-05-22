<?php declare(strict_types=1);

namespace SBSEDV\Bundle\PaginatorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sbsedv_paginator');
        $rootNode = $treeBuilder->getRootNode();

        $this->addOffsetNormalizerSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Add the "offset_normalizer" section.
     */
    private function addOffsetNormalizerSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('offset_normalizer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('query_parameters')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('page')
                                    ->info('Name of query parameter that contains the pagination page.')
                                    ->defaultValue('page')
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('offset')
                                    ->info('Name of query parameter that contains the database offset.')
                                    ->defaultValue('offset')
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('limit')
                                    ->info('Name of query parameter that contains the database limit.')
                                    ->defaultValue('limit')
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('lazy')
                                    ->info('Name of query parameter that toggles the lazy pagination.')
                                    ->defaultValue('lazyPaginator')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('default_values')
                            ->addDefaultsIfNotSet()
                                ->children()
                                    ->integerNode('limit')
                                        ->info('Default value for how many items should be loaded per page.')
                                        ->defaultValue(150)
                                    ->end()
                                    ->integerNode('max_limit')
                                        ->info('Default upper limit for how many items per page can be loaded.')
                                        ->defaultValue(1500)
                                    ->end()
                                    ->booleanNode('is_lazy')
                                        ->info('Whether paginators are all lazy by default.')
                                        ->defaultFalse()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

            ->end()
        ;
    }
}
