<?php declare(strict_types=1);

namespace SBSEDV\Bundle\PaginatorBundle\Service;

use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;
use SBSEDV\Bundle\PaginatorBundle\OffsetLimit\OffsetLimitConfig;
use SBSEDV\Bundle\PaginatorBundle\OffsetLimit\OffsetLimitPaginator;
use Symfony\Component\HttpFoundation\Request;

class PaginatorFactory
{
    public function __construct(
        private readonly string $pageQueryParameter,
        private readonly string $offsetQueryParameter,
        private readonly string $limitQueryParameter,
        private readonly string $lazyQueryParameter,
        private readonly int $defaultLimit,
        private readonly int $maxLimit,
        private readonly bool $defaultIsLazy,
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
     * @param OrmPaginator $paginator     The doctrine/orm paginator.
     * @param Request      $request       The http-foundation request.
     * @param int          $defaultPage   [optional] The default "page" used to calculate the OFFSET clause value.
     * @param int          $defaultLimit  [optional] The default LIMIT clause value.
     * @param int          $maxLimit      [optional] The maximum allowed value of the LIMIT clause.
     * @param bool         $defaultIsLazy [optional] Whether the paginator is lazy by default.
     */
    public function createOffsetLimitPaginatorFromRequest(OrmPaginator $paginator, Request $request, int $defaultPage = 1, ?int $defaultLimit = null, ?int $maxLimit = null, ?bool $defaultIsLazy = null, ?callable $modifyConfigCallable = null): OffsetLimitPaginator
    {
        $defaultLimit ??= $this->defaultLimit;

        $limit = $request->query->getInt($this->limitQueryParameter, $defaultLimit);
        if ($limit < 1 || $limit > ($maxLimit ?? $this->maxLimit)) {
            $limit = $defaultLimit;
        }

        $offset = null;
        if ($request->query->has($this->offsetQueryParameter)) {
            $offset = $request->query->getInt($this->offsetQueryParameter);

            if ($offset < 0) {
                // basically if an invalid offset (a negative one) was provided,
                // we use the $defaultPage argument
                $offset = self::calculateOffset($defaultPage, $limit);
            }
        } else {
            $page = $request->query->getInt($this->pageQueryParameter, $defaultPage);

            // negative pages can not exist
            if ($page < 1) {
                $page = $defaultPage;
            }

            $offset = self::calculateOffset($page, $limit);
        }

        $isLazy = $request->query->getBoolean($this->lazyQueryParameter, $defaultIsLazy ?? $this->defaultIsLazy);

        $config = new OffsetLimitConfig($offset, $limit, $isLazy);

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
    private static function calculateOffset(int $page, int $limit = 150): int
    {
        return ($page * $limit) - $limit;
    }
}
