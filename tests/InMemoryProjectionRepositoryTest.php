<?php

namespace PfaffKIT\Essa\Tests;

use PfaffKIT\Essa\EventSourcing\Projection\Projection as ProjectionInterface;
use PfaffKIT\Essa\EventSourcing\Projection\ProjectionRepository;
use PfaffKIT\Essa\EventSourcing\Projection\Projector;
use PfaffKIT\Essa\Shared\Id;
use PfaffKIT\Essa\Shared\Identity;
use PfaffKIT\Essa\Test\InMemoryProjectionRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

class TestProjection implements ProjectionInterface
{
    public Identity $id;
    public array $data = [];

    public function __construct(array $data = [])
    {
        $this->id = Id::new();
        $this->data = $data;
    }

    public static function getProjectionName(): string
    {
        return 'test_projection';
    }

    /**
     * @return class-string<Projector>
     */
    public static function getProjectorClass(): string
    {
        return 'fake';
    }

    /**
     * @return class-string<ProjectionRepository>
     */
    public static function getRepositoryClass(): string
    {
        return InMemoryProjectionRepository::class;
    }

    public function toArray(): array
    {
        return array_merge(['id' => (string) $this->id], $this->data);
    }

    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }
}

#[CoversClass(InMemoryProjectionRepository::class)]
class InMemoryProjectionRepositoryTest extends TestCase
{
    private InMemoryProjectionRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryProjectionRepository();
    }

    private function createProjection(array $data): TestProjection
    {
        return new TestProjection($data);
    }

    public function testFindByWithOrOperator(): void
    {
        // Arrange
        $projection1 = $this->createProjection(['status' => 'active', 'age' => 25]);
        $projection2 = $this->createProjection(['status' => 'inactive', 'age' => 30]);
        $projection3 = $this->createProjection(['status' => 'pending', 'age' => 35]);

        $this->repository->save($projection1);
        $this->repository->save($projection2);
        $this->repository->save($projection3);

        // Act - Find projections where status is 'active' OR age is 30
        $results = $this->repository->findBy([
            '$or' => [
                ['status' => 'active'],
                ['age' => 30],
            ],
        ]);

        // Assert
        $this->assertCount(2, $results);
        $this->assertContains($projection1, $results);
        $this->assertContains($projection2, $results);
        $this->assertNotContains($projection3, $results);
    }

    public function testFindByWithNestedOrOperators(): void
    {
        // Arrange
        $projection1 = $this->createProjection(['type' => 'user', 'status' => 'active', 'age' => 25]);
        $projection2 = $this->createProjection(['type' => 'admin', 'status' => 'inactive', 'age' => 30]);
        $projection3 = $this->createProjection(['type' => 'user', 'status' => 'pending', 'age' => 35]);
        $projection4 = $this->createProjection(['type' => 'guest', 'status' => 'active', 'age' => 40]);

        $this->repository->save($projection1);
        $this->repository->save($projection2);
        $this->repository->save($projection3);
        $this->repository->save($projection4);

        // Act - Find projections where (status is 'active' OR age > 30) AND type is 'user'
        $results = $this->repository->findBy([
            'type' => 'user',
            '$or' => [
                ['status' => 'active'],
                ['age' => ['$gt' => 30]],
            ],
        ]);

        // Assert
        $this->assertCount(2, $results);
        $this->assertContains($projection1, $results); // type=user, status=active
        $this->assertContains($projection3, $results); // type=user, age=35
        $this->assertNotContains($projection2, $results); // type=admin
        $this->assertNotContains($projection4, $results); // type=guest
    }

    public function testFindByWithMultipleOrConditions(): void
    {
        // Arrange
        $projection1 = $this->createProjection(['category' => 'A', 'status' => 'new', 'priority' => 'high']);
        $projection2 = $this->createProjection(['category' => 'B', 'status' => 'new', 'priority' => 'low']);
        $projection3 = $this->createProjection(['category' => 'A', 'status' => 'old', 'priority' => 'high']);
        $projection4 = $this->createProjection(['category' => 'C', 'status' => 'new', 'priority' => 'medium']);

        $this->repository->save($projection1);
        $this->repository->save($projection2);
        $this->repository->save($projection3);
        $this->repository->save($projection4);

        // Act - Find projections where (category is 'A' AND status is 'new') OR (priority is 'high')
        $results = $this->repository->findBy([
            '$or' => [
                ['category' => 'A', 'status' => 'new'],
                ['priority' => 'high'],
            ],
        ]);

        // Assert
        $this->assertCount(2, $results);
        $this->assertContains($projection1, $results); // category=A, status=new
        $this->assertContains($projection3, $results); // priority=high
        $this->assertNotContains($projection2, $results);
        $this->assertNotContains($projection4, $results);
    }
}
