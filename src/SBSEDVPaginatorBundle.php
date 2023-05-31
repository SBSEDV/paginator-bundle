<?php declare(strict_types=1);

namespace SBSEDV\Bundle\PaginatorBundle;

use SBSEDV\Bundle\PaginatorBundle\Service\PaginatorFactory;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SBSEDVPaginatorBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definitions.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()
            ->set('sbsedv_paginator.query_parameter', $config['query_parameter'])
        ;

        $container->import('../config/services/factory.php');
        $container->import('../config/services/offset_limit_paginator_normalizer.php');

        $container->services()
            ->get(PaginatorFactory::class)
            ->arg('$defaultPageSize', $config['default_page_size'])
            ->arg('$defaultMaxPageSize', $config['default_max_page_size'])
        ;
    }
}
