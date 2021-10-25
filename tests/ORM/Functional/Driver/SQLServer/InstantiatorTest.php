<?php

declare(strict_types=1);

namespace Cycle\ORM\Tests\Functional\Driver\SQLServer;

// phpcs:ignore
use Cycle\ORM\Tests\Functional\Driver\Common\InstantiatorTest as CommonTest;

/**
 * @group driver
 * @group driver-sqlserver
 */
class InstantiatorTest extends CommonTest
{
    public const DRIVER = 'sqlserver';
}