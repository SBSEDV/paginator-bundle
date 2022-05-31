<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\PaginatorBundle\Serializer\Normalizer\OffsetLimitPaginatorNormalizer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(OffsetLimitPaginatorNormalizer::class)
            ->args([
                '$requestStack' => service(RequestStack::class),
                '$urlGenerator' => service(UrlGeneratorInterface::class),
                '$queryParameter' => param('sbsedv_paginator.query_parameter'),
            ])
            ->tag('serializer.normalizer')
    ;
};
