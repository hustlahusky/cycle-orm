<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Cycle\Selector;

use Spiral\Database\Query\SelectQuery;

/**
 * Provides the ability to scope query and load necessary relations into the loader.
 */
class QueryScope implements ScopeInterface
{
    /** @var array */
    private $where = [];

    /** @var array */
    private $orderBy = [];

    /**
     * @param array $where
     * @param array $orderBy
     */
    public function __construct(array $where, array $orderBy = [])
    {
        $this->where = $where;
        $this->orderBy = $orderBy;
    }

    /**
     * @inheritdoc
     */
    public function apply(SelectQuery $query, AbstractLoader $loader)
    {
        $query->where($this->where)->orderBy($this->orderBy);
    }
}