<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\PaginatorBundle\Service\PaginatorFactory;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(PaginatorFactory::class)
            ->args([
                '$pageQueryParameter' => param('sbsedv_paginator.query_params.page'),
                '$offsetQueryParameter' => param('sbsedv_paginator.query_params.offset'),
                '$limitQueryParameter' => param('sbsedv_paginator.query_params.limit'),

                '$defaultLimit' => abstract_arg('default limit value'),
                '$maxLimit' => abstract_arg('default max_limit value'),
            ])
    ;
};
