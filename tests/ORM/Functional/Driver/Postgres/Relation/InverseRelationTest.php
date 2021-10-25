<?php

declare(strict_types=1);

namespace Cycle\ORM\Tests\Functional\Driver\Postgres\Relation;

// phpcs:ignore
use Cycle\ORM\Tests\Functional\Driver\Common\Relation\InverseRelationTest as CommonTest;

/**
 * @group driver
 * @group driver-postgres
 */
class InverseRelationTest extends CommonTest
{
    public const DRIVER = 'postgres';
}