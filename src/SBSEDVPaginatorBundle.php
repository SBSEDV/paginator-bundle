<?php declare(strict_types=1);

namespace SBSEDV\Bundle\PaginatorBundle;

use SBSEDV\Bundle\PaginatorBundle\Service\PaginatorFactory;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SBSEDVPaginatorBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definitions/default_values.php');
        $definition->import('../config/definitions/query_parameters.php');
    }

    /**
     * {@inheritdoc}
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()
            ->set('sbsedv_paginator.query_params.page', $config['query_parameters']['page'])
            ->set('sbsedv_paginator.query_params.offset', $config['query_parameters']['offset'])
            ->set('sbsedv_paginator.query_params.limit', $config['query_parameters']['limit'])
        ;

        $container->import('../config/services/factory.php');
        $container->import('../config/services/offset_limit_paginator_normalizer.php');

        $container->services()
            ->get(PaginatorFactory::class)
            ->arg('$defaultLimit', $config['default_values']['limit'])
            ->arg('$maxLimit', $config['default_values']['max_limit'])
        ;
    }
}
