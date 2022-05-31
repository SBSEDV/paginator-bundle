<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\PaginatorBundle\Service\PaginatorFactory;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(PaginatorFactory::class)
            ->args([
                '$queryParameter' => param('sbsedv_paginator.query_parameter'),
                '$defaultPageSize' => abstract_arg('default page size'),
                '$defaultMaxPageSize' => abstract_arg('default maximum page size'),
            ])
    ;
};
