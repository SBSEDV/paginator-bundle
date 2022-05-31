<?php declare(strict_types=1);

namespace Symfony\Component\Config\Definition\Configurator;

return function (DefinitionConfigurator $definition): void {
    $definition
        ->rootNode()
            ->children()
                ->integerNode('default_page_size')
                    ->info('Default value for how many items should be loaded per request.')
                    ->defaultValue(150)
                ->end()

                ->integerNode('default_max_page_size')
                    ->info('Default upper limit for how many items can be loaded per request.')
                    ->defaultValue(1500)
                ->end()

                ->scalarNode('query_parameter')
                    ->info('Name of query parameter that contains the pagination options.')
                    ->defaultValue('page')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ->end()
    ;
};
