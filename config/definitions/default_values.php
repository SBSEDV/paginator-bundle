<?php declare(strict_types=1);

namespace Symfony\Component\Config\Definition\Configurator;

return function (DefinitionConfigurator $definition): void {
    $definition
        ->rootNode()
            ->children()
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
                    ->end()
                ->end()
            ->end()
        ->end()
    ;
};
