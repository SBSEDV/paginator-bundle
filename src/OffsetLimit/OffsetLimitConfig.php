<?php declare(strict_types=1);

namespace SBSEDV\Bundle\PaginatorBundle\OffsetLimit;

final class OffsetLimitConfig
{
    public function __construct(
        private int $offset,
        private int $limit
    ) {
    }

    /**
     * The database offset.
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Set the database offset.
     *
     * @param int $offset The database offset.
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * The total amount of items per page (LIMIT).
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Set the total amount of items per page (LIMIT).
     *
     * @param int $limit The total amount of items per page (LIMIT).
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }
}
