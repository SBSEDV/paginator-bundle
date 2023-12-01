<?php declare(strict_types=1);

namespace SBSEDV\Bundle\PaginatorBundle\OffsetLimit;

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @template T of object
 *
 * @implements \IteratorAggregate<array-key, T>
 */
final class OffsetLimitPaginator implements \IteratorAggregate, \Countable // @phpstan-ignore-line
{
    private ?int $count = null;

    /** @var \IteratorIterator<array-key, T, Paginator<T>> */
    private readonly \IteratorIterator $iterator;

    /**
     * @param Paginator<T>      $paginator The doctrine/orm paginator.
     * @param OffsetLimitConfig $config    The paginator configuration.
     */
    public function __construct(
        private readonly Paginator $paginator, // @phpstan-ignore-line
        private readonly OffsetLimitConfig $config
    ) {
        $query = $this->paginator->getQuery();

        if (0 === $query->getFirstResult()) {
            $query->setFirstResult($config->getOffset());
        }

        if (null === $query->getMaxResults()) {
            $query->setMaxResults($config->getLimit());
        }

        $this->iterator = new \IteratorIterator($this->paginator);
    }

    /**
     * @return \Traversable<array-key, T>
     */
    public function getIterator(): \Traversable
    {
        return $this->iterator;
    }

    /**
     * @return Paginator<T>
     */
    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }

    /**
     * The total amount of items on the current pagination page.
     */
    public function count(): int
    {
        if (null === $this->count) {
            $count = \count(\iterator_to_array($this->iterator));
            $this->count = $count;
        }

        return $this->count;
    }

    /**
     * The configuration used to create this paginated query.
     */
    public function getConfig(): OffsetLimitConfig
    {
        return $this->config;
    }

    /**
     * The total amount of items that this pagination query can find.
     */
    public function getTotalCount(): int
    {
        return $this->paginator->count();
    }

    /**
     * The total amount of pages that this pagination query can find.
     */
    public function getTotalPages(): int
    {
        $totalPages = (int) \ceil($this->getTotalCount() / $this->getConfig()->getLimit());

        return $totalPages === 0 ? 1 : $totalPages;
    }
}
