<?php

declare(strict_types=1);

namespace Cycle\ORM\Tests\Functional\Driver\SQLite\Inheritance\JTI\Mapper;

// phpcs:ignore
use Cycle\ORM\Tests\Functional\Driver\Common\Inheritance\JTI\Mapper\ParentClassRelationsClasslessMapperTest as CommonTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class ParentClassRelationsClasslessMapperTest extends CommonTest
{
    public const DRIVER = 'sqlite';
}