<?php

declare(strict_types=1);

namespace Cycle\ORM\Tests\Inheritance\JTI;

use Cycle\ORM\Heap\Heap;
use Cycle\ORM\Mapper\Mapper;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Tests\Inheritance\Fixture\Employee;
use Cycle\ORM\Tests\Inheritance\Fixture\Engineer;
use Cycle\ORM\Tests\Inheritance\Fixture\Manager;
use Cycle\ORM\Tests\Inheritance\Fixture\Programator;
use Cycle\ORM\Transaction;

abstract class SimpleCasesTest extends JtiBaseTest
{
    protected const
        EMPLOYEE_1 = ['id' => 1, 'name' => 'John', 'age' => 38],
        EMPLOYEE_2 = ['id' => 2, 'name' => 'Anton', 'age' => 35],
        EMPLOYEE_3 = ['id' => 3, 'name' => 'Kentarius', 'age' => 27],
        EMPLOYEE_4 = ['id' => 4, 'name' => 'Valeriy', 'age' => 32],

        ENGINEER_2 = ['id' => 2, 'level' => 8],
        ENGINEER_4 = ['id' => 4, 'level' => 10],

        PROGRAMATOR_2 = ['id' => 2, 'language' => 'php'],
        PROGRAMATOR_4 = ['id' => 4, 'language' => 'go'],

        MANAGER_1 = ['id' => 1, 'rank' => 'top'],
        MANAGER_3 = ['id' => 3, 'rank' => 'bottom'],

        ENGINEER_2_PK = 2,
        PROGRAMATOR_2_PK = self::ENGINEER_2_PK,

        EMPLOYEE_1_LOADED = self::EMPLOYEE_1,
        EMPLOYEE_2_LOADED = self::EMPLOYEE_2,
        EMPLOYEE_3_LOADED = self::EMPLOYEE_3,
        EMPLOYEE_4_LOADED = self::EMPLOYEE_4,

        ENGINEER_2_LOADED = self::ENGINEER_2 + self::EMPLOYEE_2_LOADED,
        ENGINEER_4_LOADED = self::ENGINEER_4 + self::EMPLOYEE_4_LOADED,

        PROGRAMATOR_2_LOADED = self::PROGRAMATOR_2 + self::ENGINEER_2_LOADED,
        PROGRAMATOR_4_LOADED = self::PROGRAMATOR_4 + self::ENGINEER_4_LOADED,

        MANAGER_1_LOADED = self::MANAGER_1 + self::EMPLOYEE_1_LOADED,
        MANAGER_3_LOADED = self::MANAGER_3 + self::EMPLOYEE_3_LOADED,

        EMPLOYEE_ALL_LOADED = [
            self::EMPLOYEE_1_LOADED,
            self::EMPLOYEE_2_LOADED,
            self::EMPLOYEE_3_LOADED,
            self::EMPLOYEE_4_LOADED,
        ],
        EMPLOYEE_INHERITED_LOADED = [
            self::MANAGER_1_LOADED,
            self::PROGRAMATOR_2_LOADED,
            self::MANAGER_3_LOADED,
            self::PROGRAMATOR_4_LOADED,
        ],
        ENGINEER_ALL_LOADED = [self::ENGINEER_2_LOADED, self::ENGINEER_4_LOADED],
        PROGRAMATOR_ALL_LOADED = [self::PROGRAMATOR_2_LOADED, self::PROGRAMATOR_4_LOADED],
        MANAGER_ALL_LOADED = [self::MANAGER_1_LOADED, self::MANAGER_3_LOADED],

        EMPLOYEE_ROLE = 'employee',
        ENGINEER_ROLE = 'engineer',
        MANAGER_ROLE = 'manager',
        PROGRAMATOR_ROLE = 'programator';

    public function setUp(): void
    {
        parent::setUp();

        $this->makeTable('employee', [
            'id'          => 'integer',
            'name_column' => 'string',
            'age'         => 'integer,nullable',
        ], pk: ['id']);
        $this->makeTable('engineer', [
            'id'        => 'integer',
            'level'     => 'integer',
        ], fk: [
            'id' => ['table' => 'employee', 'column' => 'id']
        ], pk: ['id']);
        $this->makeTable('programator', [
            'id'        => 'integer',
            'language' => 'string',
        ], fk: [
            'id' => ['table' => 'engineer', 'column' => 'id']
        ], pk: ['id']);
        $this->makeTable('manager', [
            'id'        => 'integer',
            'rank'      => 'string',
        ], fk: [
            'id' => ['table' => 'employee', 'column' => 'id']
        ], pk: ['id']);

        $this->getDatabase()->table('employee')->insertMultiple(
            ['id', 'name_column', 'age'],
            [
                self::EMPLOYEE_1,
                self::EMPLOYEE_2,
                self::EMPLOYEE_3,
                self::EMPLOYEE_4,
            ]
        );

        $this->getDatabase()->table('engineer')->insertMultiple(
            ['id', 'level'],
            [
                self::ENGINEER_2,
                self::ENGINEER_4,
            ]
        );

        $this->getDatabase()->table('programator')->insertMultiple(
            ['id', 'language'],
            [
                self::PROGRAMATOR_2,
                self::PROGRAMATOR_4,
            ]
        );

        $this->getDatabase()->table('manager')->insertMultiple(
            ['id', 'rank'],
            [
                self::MANAGER_1,
                self::MANAGER_3,
            ]
        );
    }

    protected function getSchemaArray(): array
    {
        return [
            Employee::class => [
                SchemaInterface::ROLE        => 'employee',
                SchemaInterface::MAPPER      => Mapper::class,
                SchemaInterface::DATABASE    => 'default',
                SchemaInterface::TABLE       => 'employee',
                SchemaInterface::PRIMARY_KEY => 'id',
                SchemaInterface::COLUMNS     => ['id', 'name' => 'name_column', 'age'],
                SchemaInterface::TYPECAST    => ['id' => 'int', 'age' => 'int'],
                SchemaInterface::SCHEMA      => [],
                SchemaInterface::RELATIONS   => [],
            ],
            Engineer::class => [
                SchemaInterface::ROLE        => 'engineer',
                SchemaInterface::MAPPER      => Mapper::class,
                SchemaInterface::DATABASE    => 'default',
                SchemaInterface::TABLE       => 'engineer',
                SchemaInterface::PARENT      => 'employee',
                SchemaInterface::PRIMARY_KEY => 'id',
                SchemaInterface::COLUMNS     => ['id', 'level'],
                SchemaInterface::TYPECAST    => ['id' => 'int', 'level' => 'int'],
                SchemaInterface::SCHEMA      => [],
                SchemaInterface::RELATIONS   => [],
            ],
            Programator::class => [
                SchemaInterface::ROLE        => 'programator',
                SchemaInterface::MAPPER      => Mapper::class,
                SchemaInterface::DATABASE    => 'default',
                SchemaInterface::TABLE       => 'programator',
                SchemaInterface::PARENT      => 'engineer',
                SchemaInterface::PRIMARY_KEY => 'id',
                SchemaInterface::COLUMNS     => ['id', 'language'],
                SchemaInterface::TYPECAST    => ['id' => 'int'],
                SchemaInterface::SCHEMA      => [],
                SchemaInterface::RELATIONS   => [],
            ],
            Manager::class => [
                SchemaInterface::ROLE        => 'manager',
                SchemaInterface::MAPPER      => Mapper::class,
                SchemaInterface::DATABASE    => 'default',
                SchemaInterface::TABLE       => 'manager',
                SchemaInterface::PARENT      => 'employee',
                SchemaInterface::PRIMARY_KEY => 'id',
                SchemaInterface::COLUMNS     => ['id', 'rank'],
                SchemaInterface::TYPECAST    => ['id' => 'int'],
                SchemaInterface::SCHEMA      => [],
                SchemaInterface::RELATIONS   => [],
            ],
        ];
    }

    // Select

    public function testSelectEmployeeHierarchyByPK(): void
    {
        $entity = (new Select($this->orm, static::EMPLOYEE_ROLE))
            ->wherePK(static::ENGINEER_2_PK)
            ->fetchOne();

        $this->assertInstanceOf(Programator::class, $entity);
    }

    public function testSelectEmployeeAllDataWithInheritance(): void
    {
        $selector = new Select($this->orm, static::EMPLOYEE_ROLE);

        $this->assertEquals(static::EMPLOYEE_INHERITED_LOADED, $selector->fetchData());
    }

    public function testSelectEmployeeAllDataWithoutInheritance(): void
    {
        $selector = (new Select($this->orm, static::EMPLOYEE_ROLE))
            ->loadSubclasses(false);

        $this->assertEquals(static::EMPLOYEE_ALL_LOADED, $selector->fetchData());
    }

    public function testSelectEmployeeDataFirstWithInheritance(): void
    {
        $selector = (new Select($this->orm, static::EMPLOYEE_ROLE))->limit(1);

        $this->assertEquals(static::MANAGER_1_LOADED, $selector->fetchData()[0]);
    }

    public function testSelectEmployeeDataFirstWithoutInheritance(): void
    {
        $selector = (new Select($this->orm, static::EMPLOYEE_ROLE))
            ->loadSubclasses(false)
            ->limit(1);

        $this->assertEquals(static::EMPLOYEE_1_LOADED, $selector->fetchData()[0]);
    }

    public function testSelectEngineerAllDataWithInheritance(): void
    {
        $selector = (new Select($this->orm, static::ENGINEER_ROLE));

        $this->assertEquals(static::PROGRAMATOR_ALL_LOADED, $selector->fetchData());
    }

    public function testSelectEngineerAllDataWithoutInheritance(): void
    {
        $selector = (new Select($this->orm, static::ENGINEER_ROLE))
            ->loadSubclasses(false);

        $this->assertEquals(static::ENGINEER_ALL_LOADED, $selector->fetchData());
    }

    public function testSelectEngineerDataFirstWithInheritance(): void
    {
        $selector = (new Select($this->orm, static::ENGINEER_ROLE))->limit(1);

        $this->assertEquals(static::PROGRAMATOR_2_LOADED, $selector->fetchData()[0]);
    }

    public function testSelectEngineerDataFirstWithoutInheritance(): void
    {
        $selector = (new Select($this->orm, static::ENGINEER_ROLE))
            ->loadSubclasses(false)
            ->limit(1);

        $this->assertEquals(static::ENGINEER_2_LOADED, $selector->fetchData()[0]);
    }

    public function testSelectEngineerEntityFirstWithInheritance(): void
    {
        $selector = (new Select($this->orm, static::ENGINEER_ROLE))->limit(1);

        $this->assertInstanceof(Programator::class, $selector->fetchOne());
    }

    public function testSelectProgramatorAllData(): void
    {
        $selector = (new Select($this->orm, static::PROGRAMATOR_ROLE));

        $this->assertEquals(static::PROGRAMATOR_ALL_LOADED, $selector->fetchData());
    }

    public function testSelectProgramatorDataFirst(): void
    {
        $selector = (new Select($this->orm, static::PROGRAMATOR_ROLE))->limit(1);

        $this->assertEquals(static::PROGRAMATOR_2_LOADED, $selector->fetchData()[0]);
    }

    public function testSelectManagerAllData(): void
    {
        $selector = (new Select($this->orm, static::MANAGER_ROLE));

        $this->assertEquals(static::MANAGER_ALL_LOADED, $selector->fetchData());
    }

    public function testSelectManagerDataFirst(): void
    {
        $selector = (new Select($this->orm, static::MANAGER_ROLE))->limit(1);

        $this->assertEquals(static::MANAGER_1_LOADED, $selector->fetchData()[0]);
    }

    // Persist

    public function testProgramatorNoChanges(): void
    {
        $programator = (new Select($this->orm, static::PROGRAMATOR_ROLE))
            ->wherePK(static::PROGRAMATOR_2_PK)->fetchOne();

        $this->captureWriteQueries();
        $this->save($programator);
        $this->assertNumWrites(0);
    }

    public function testChangeAndPersistProgramator(): void
    {
        /** @var Programator $programator */
        $programator = (new Select($this->orm, static::PROGRAMATOR_ROLE))
            ->wherePK(static::PROGRAMATOR_2_PK)->fetchOne();
        $programator->language = 'Kotlin';

        $this->captureWriteQueries();
        $this->save($programator);
        $this->assertNumWrites(1);

        $this->captureWriteQueries();
        $this->save($programator);
        $this->assertNumWrites(0);

        /** @var Programator $programator */
        $programator = (new Select($this->orm->withHeap(new Heap()), static::PROGRAMATOR_ROLE))
            ->wherePK(static::PROGRAMATOR_2_PK)->fetchOne();
        $this->assertSame('Kotlin', $programator->language);
    }

    public function testChangeParentsFieldsAndPersistProgramator(): void
    {
        /** @var Programator $programator */
        $programator = (new Select($this->orm, static::PROGRAMATOR_ROLE))
            ->wherePK(static::PROGRAMATOR_2_PK)->fetchOne();
        $programator->language = 'Kotlin';
        $programator->level = 99;
        $programator->name = 'Thomas';

        $this->captureWriteQueries();
        $this->save($programator);
        $this->assertNumWrites(3);

        $this->captureWriteQueries();
        $this->save($programator);
        $this->assertNumWrites(0);

        /** @var Programator $programator */
        $programator = (new Select($this->orm->withHeap(new Heap()), static::PROGRAMATOR_ROLE))
            ->wherePK(static::PROGRAMATOR_2_PK)->fetchOne();
        $this->assertSame('Kotlin', $programator->language);
        $this->assertSame(99, $programator->level);
        $this->assertSame('Thomas', $programator->name);
    }

    public function testCreateProgramator(): void
    {
        $programator = new Programator();
        $programator->name = 'Merlin';
        $programator->level = 50;
        $programator->language = 'VanillaJS';

        $this->captureWriteQueries();
        $this->save($programator);
        $this->assertNumWrites(3);

        $this->captureWriteQueries();
        $this->save($programator);
        $this->assertNumWrites(0);

        /** @var Programator $programator */
        $programator = (new Select($this->orm->withHeap(new Heap()), Programator::class))
            ->wherePK($programator->id)
            ->fetchOne();
        $this->assertSame('Merlin', $programator->name);
        $this->assertSame(50, $programator->level);
        $this->assertSame('VanillaJS', $programator->language);
    }

    public function testRemoveEngineer(): void
    {
        /** @var Engineer $entity */
        $entity = (new Select($this->orm, static::ENGINEER_ROLE))
            ->loadSubclasses(false)
            ->wherePK(static::ENGINEER_2_PK)->fetchOne();

        $this->captureWriteQueries();
        (new Transaction($this->orm))->delete($entity)->run();
        $this->assertNumWrites(1);

        $this->captureWriteQueries();
        (new Transaction($this->orm))->delete($entity)->run();
        $this->assertNumWrites(0);

        // todo load without inheritance
        $this->assertNull((new Select($this->orm, static::PROGRAMATOR_ROLE))->wherePK(static::ENGINEER_2_PK)->fetchOne());
        $this->assertNull((new Select($this->orm, static::ENGINEER_ROLE))
            ->loadSubclasses(false)
            ->wherePK(static::ENGINEER_2_PK)->fetchOne());
        $this->assertNotNull((new Select($this->orm, static::EMPLOYEE_ROLE))
            ->loadSubclasses(false)
            ->wherePK(static::ENGINEER_2_PK)->fetchOne());
    }
}