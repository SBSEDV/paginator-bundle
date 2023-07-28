<?php declare(strict_types=1);

namespace SBSEDV\Bundle\PaginatorBundle\Serializer\Normalizer;

use Psr\Link\EvolvableLinkProviderInterface;
use SBSEDV\Bundle\PaginatorBundle\OffsetLimit\OffsetLimitPaginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\WebLink\EventListener\AddLinkHeaderListener;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\Link;

/**
 * @template T of object
 */
class OffsetLimitPaginatorNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator,
        private readonly string $queryParameter,
    ) {
    }

    /**
     * @param OffsetLimitPaginator<T> $object
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        $request = $this->requestStack->getCurrentRequest();

        $currentPage = self::calculatePage($object->getConfig()->getOffset(), $object->getConfig()->getLimit());

        $totalPages = $object->getTotalPages();

        $hasMore = $totalPages > $currentPage;

        $data = [
            'items' => $this->normalizer->normalize($object->getData(), $format, $context),
            'pagination' => [
                'current_page' => $currentPage,
                'items_current_page' => $object->count(),
                'items_per_page' => $object->getConfig()->getLimit(),
                'total_pages' => $totalPages,
                'total_items' => $object->getTotalCount(),
                'more' => $hasMore,
                '_links' => [
                    'self' => [
                        'href' => $this->generateUrl($request, $object, $context, $currentPage),
                    ],
                ],
            ],
        ];

        $totalPages = $object->getTotalPages();

        if ($currentPage > 1) {
            $data['pagination']['_links']['first'] = [
                'href' => $this->generateUrl($request, $object, $context, 1),
            ];

            $prevPage = $currentPage <= $totalPages ? $currentPage - 1 : $totalPages;

            $data['pagination']['_links']['prev'] = [
                'href' => $this->generateUrl($request, $object, $context, $prevPage),
            ];
        }

        if ($hasMore) {
            $data['pagination']['_links']['next'] = [
                'href' => $this->generateUrl($request, $object, $context, $currentPage + 1),
            ];

            $data['pagination']['_links']['last'] = [
                'href' => $this->generateUrl($request, $object, $context, $totalPages),
            ];
        }

        if (null !== $request && \class_exists(AddLinkHeaderListener::class)) {
            if (!($request->attributes->get('_links') instanceof EvolvableLinkProviderInterface)) {
                $request->attributes->set('_links', new GenericLinkProvider());
            }

            /** @var EvolvableLinkProviderInterface $linkProvider */
            $linkProvider = $request->attributes->get('_links');

            foreach ($data['pagination']['_links'] as $key => $value) {
                $linkProvider = $linkProvider->withLink(new Link($key, $value['href']));
            }

            $request->attributes->set('_links', $linkProvider);
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof OffsetLimitPaginator;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            OffsetLimitPaginator::class => __CLASS__ === static::class,
        ];
    }

    /**
     * @param OffsetLimitPaginator<T> $object
     */
    private function generateUrl(?Request $request, OffsetLimitPaginator $object, array $context, int $page): string
    {
        $routeName = $context['_route'] ?? $request?->attributes->get('_route') ?? throw new \LogicException('You must provide the "_route" context in non http-foundation applications.');

        $routeParams = $request?->attributes->all('_route_params') ?? [];
        $queryParams = $context['_query_params'] ?? $request?->query->all() ?? [];

        $params = $routeParams + ($context['_route_params'] ?? []) + $queryParams;

        $params[$this->queryParameter]['size'] = $object->getConfig()->getLimit();
        $params[$this->queryParameter]['number'] = $page;

        return $this->urlGenerator->generate($routeName, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Calculate the pagination page from the database LIMIT clause.
     *
     * @param int $offset The database offset.
     * @param int $limit  The database limit.
     *
     * @return int The pagination page.
     */
    private static function calculatePage(int $offset, int $limit): int
    {
        return (int) \floor($offset / $limit) + 1;
    }
}
