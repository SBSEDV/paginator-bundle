<?php declare(strict_types=1);

namespace SBSEDV\Bundle\PaginatorBundle\OffsetLimit;

use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;

/**
 * @template T of object
 */
final class OffsetLimitPaginator implements \IteratorAggregate, \Countable // @phpstan-ignore-line
{
    private array $data;
    private \Traversable $iterator;
    private int $count;

    /**
     * @param \Doctrine\ORM\Tools\Pagination\Paginator<T> $paginator The doctrine/orm paginator.
     * @param OffsetLimitConfig                           $config    The paginator configuration object used.
     */
    public function __construct(
        private readonly OrmPaginator $paginator, // @phpstan-ignore-line
        private readonly OffsetLimitConfig $config
    ) {
        $query = $this->paginator->getQuery();

        if (null === $query->getFirstResult()) {
            $query->setFirstResult($config->getOffset());
        }

        if (null === $query->getMaxResults()) {
            $query->setMaxResults($config->getLimit());
        }

        $this->data = \iterator_to_array($this->paginator);

        $this->updateIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return $this->iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * The current page data.
     *
     * @return T[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Update the current page data.
     *
     * @param T[] $data The current page data.
     */
    public function setData(array $data): self // @phpstan-ignore-line
    {
        $this->data = $data;

        $this->updateIterator();

        return $this;
    }

    /**
     * Apply a filter function to each element.
     *
     * @param callable $cb           The filter function.
     * @param bool     $preserveKeys [optional] Whether to preserve the original array keys.
     */
    public function filter(callable $cb, bool $preserveKeys = false): self
    {
        $prevCount = $this->count();

        $filterd = \array_filter($this->getData(), $cb);

        if (!$preserveKeys) {
            $filterd = \array_values($filterd);
        }

        $this->setData($filterd);

        if ($prevCount > $this->count()) {
            // the lazy check if "more" items are available
            // will always return false if items have been removed
            $this->getConfig()->setIsLazy(false);
        }

        return $this;
    }

    /**
     * The doctrine/orm paginator.
     */
    public function getOrmPaginator(): OrmPaginator // @phpstan-ignore-line
    {
        return $this->paginator;
    }

    /**
     * The paginator info object used to create this paginator.
     */
    public function getConfig(): OffsetLimitConfig
    {
        return $this->config;
    }

    /**
     * The total amount of items that this paginator can find.
     */
    public function getTotalCount(): int
    {
        return $this->paginator->count();
    }

    /**
     * The total amount of pages that this paginator handles.
     */
    public function getTotalPages(): int
    {
        return (int) \ceil($this->getTotalCount() / $this->getConfig()->getLimit());
    }

    /**
     * Update the internal iterator.
     */
    private function updateIterator(): void
    {
        $this->iterator = new \ArrayIterator($this->data);

        $this->count = \count($this->data);
    }
}
