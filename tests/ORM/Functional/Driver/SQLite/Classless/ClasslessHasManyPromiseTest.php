<?php

declare(strict_types=1);

namespace Cycle\ORM\Tests\Functional\Driver\SQLite\Classless;

// phpcs:ignore
use Cycle\ORM\Tests\Functional\Driver\Common\Classless\ClasslessHasManyPromiseTest as CommonTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class ClasslessHasManyPromiseTest extends CommonTest
{
    public const DRIVER = 'sqlite';
}