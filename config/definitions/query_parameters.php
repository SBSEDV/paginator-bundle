<?php declare(strict_types=1);

namespace Symfony\Component\Config\Definition\Configurator;

return function (DefinitionConfigurator $definition): void {
    $definition
        ->rootNode()
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
                    ->end()
                ->end()
            ->end()
        ->end()
    ;
};
