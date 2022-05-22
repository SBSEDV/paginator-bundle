<?php declare(strict_types=1);

namespace SBSEDV\Bundle\PaginatorBundle\DependencyInjection;

use SBSEDV\Bundle\PaginatorBundle\Serializer\Normalizer\OffsetLimitPaginatorNormalizer;
use SBSEDV\Bundle\PaginatorBundle\Service\PaginatorFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SBSEDVPaginatorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $this->configureFactory($container, $config);
        $this->configureNormalizer($container, $config);
    }

    /**
     * Configure the paginator factory.
     */
    private function configureFactory(ContainerBuilder $container, array $config): void
    {
        $container
            ->setDefinition(PaginatorFactory::class, new Definition(PaginatorFactory::class))
            ->setArguments([
                '$pageQueryParameter' => $config['offset_normalizer']['query_parameters']['page'],
                '$offsetQueryParameter' => $config['offset_normalizer']['query_parameters']['offset'],
                '$limitQueryParameter' => $config['offset_normalizer']['query_parameters']['limit'],
                '$lazyQueryParameter' => $config['offset_normalizer']['query_parameters']['lazy'],
                '$defaultLimit' => $config['offset_normalizer']['default_values']['limit'],
                '$maxLimit' => $config['offset_normalizer']['default_values']['max_limit'],
                '$defaultIsLazy' => $config['offset_normalizer']['default_values']['is_lazy'],
            ])
        ;
    }

    /**
     * Configure the normalizers.
     */
    private function configureNormalizer(ContainerBuilder $container, array $config): void
    {
        $container
            ->setDefinition(OffsetLimitPaginatorNormalizer::class, new Definition(OffsetLimitPaginatorNormalizer::class))
            ->setArguments([
                '$requestStack' => new Reference(RequestStack::class),
                '$urlGenerator' => new Reference(UrlGeneratorInterface::class),
                '$pageQueryParameter' => $config['offset_normalizer']['query_parameters']['page'],
                '$limitQueryParameter' => $config['offset_normalizer']['query_parameters']['limit'],
                '$lazyQueryParameter' => $config['offset_normalizer']['query_parameters']['lazy'],
            ])
            ->addTag('serializer.normalizer')
        ;
    }
}
