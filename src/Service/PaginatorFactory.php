<?php declare(strict_types=1);

namespace SBSEDV\Bundle\PaginatorBundle\Service;

use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use SBSEDV\Bundle\PaginatorBundle\OffsetLimit\OffsetLimitConfig;
use SBSEDV\Bundle\PaginatorBundle\OffsetLimit\OffsetLimitPaginator;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

class PaginatorFactory
{
    public function __construct(
        private readonly string $queryParameter,
        private readonly int $defaultPageSize,
        private readonly int $defaultMaxPageSize,
    ) {
    }

    /**
     * Create a PaginatorConfig object from an http-foundation request.
     *
     * Negative values are converted to specified default values.
     *
     * If the specified limit is bigger than the maximum allowed limit,
     * the default limit is used.
     *
     * @template T of object
     *
     * @param OrmPaginator<T> $paginator    The doctrine/orm paginator.
     * @param Request         $request      The http-foundation request.
     * @param int             $defaultPage  [optional] The default "page" used to calculate the OFFSET clause value.
     * @param int             $defaultLimit [optional] The default LIMIT clause value.
     * @param int             $maxLimit     [optional] The maximum allowed value of the LIMIT clause.
     *
     * @return OffsetLimitPaginator<T>
     */
    public function createOffsetLimitPaginatorFromRequest(OrmPaginator $paginator, Request $request, int $defaultPage = 1, ?int $defaultLimit = null, ?int $maxLimit = null, ?callable $modifyConfigCallable = null): OffsetLimitPaginator
    {
        $defaultLimit ??= $this->defaultPageSize;

        $pageBag = new InputBag($request->query->all($this->queryParameter));

        if ($pageBag->has('size')) {
            $limit = $pageBag->getInt('size');
        } elseif ($pageBag->has('limit')) {
            $limit = $pageBag->getInt('limit');
        } else {
            $limit = $defaultLimit;
        }

        if ($limit < 1 || $limit > ($maxLimit ?? $this->defaultMaxPageSize)) {
            $limit = $defaultLimit;
        }

        $offset = null;
        if ($pageBag->has('offset')) {
            $offset = $pageBag->getInt('offset');

            if ($offset < 0) {
                // basically if an invalid offset (a negative one) was provided,
                // we use the $defaultPage argument
                $offset = self::calculateOffset($defaultPage, $limit);
            }
        } else {
            $page = $pageBag->getInt('number', $defaultPage);

            // negative pages can not exist
            if ($page < 1) {
                $page = $defaultPage;
            }

            $offset = self::calculateOffset($page, $limit);
        }

        $config = new OffsetLimitConfig($offset, $limit);

        if (null !== $modifyConfigCallable) {
            \call_user_func_array($modifyConfigCallable, [&$config]);
        }

        return new OffsetLimitPaginator($paginator, $config);
    }

    /**
     * Calculate the database offset from pagination parameters.
     *
     * @param int $page  The pagination page.
     * @param int $limit The database LIMIT clause value.
     *
     * @return int The database OFFSET clause value.
     */
    private static function calculateOffset(int $page, int $limit): int
    {
        return ($page * $limit) - $limit;
    }
}
