<?php

namespace PfaffKIT\Essa\Tests;

use PfaffKIT\Essa\EventSourcing\Projection\ProjectionManager;
use PfaffKIT\Essa\EventSourcing\Projection\ProjectionManagerInterface;
use PfaffKIT\Essa\Shared\Id;
use PfaffKIT\Essa\Tests\mocks\AnotherTestProjection;
use PfaffKIT\Essa\Tests\mocks\TestProjection;
use PfaffKIT\Essa\Tests\mocks\TestProjectionRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProjectionManager::class)]
class ProjectionManagerTest extends TestCase
{
    private ProjectionManager $manager;
    private TestProjectionRepository $testRepository;

    protected function setUp(): void
    {
        $this->testRepository = new TestProjectionRepository();
        $this->manager = new ProjectionManager([$this->testRepository]);

        parent::setUp();
    }

    public function testImplementsInterface(): void
    {
        self::assertInstanceOf(ProjectionManagerInterface::class, $this->manager);
    }

    public function testGetRepositoryReturnsCorrectRepository(): void
    {
        $repository = $this->manager->getRepository(TestProjection::class);

        self::assertSame($this->testRepository, $repository);
    }

    public function testGetRepositoryThrowsForInvalidProjectionClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement');

        $this->manager->getRepository(\stdClass::class);
    }

    public function testGetRepositoryThrowsForUnknownProjection(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No repository found');

        $this->manager->getRepository(AnotherTestProjection::class);
    }

    public function testFindReturnsProjection(): void
    {
        $projection = new TestProjection(Id::fromString('123e4567-e89b-12d3-a456-426614174000'));

        $this->testRepository->addProjection($projection);
        $result = $this->manager->find(TestProjection::class, $projection->id);

        self::assertSame($projection, $result);
    }

    public function testFindReturnsNullWhenNotFound(): void
    {
        $id = Id::fromString('00000000-0000-0000-0000-000000000000');

        $result = $this->manager->find(TestProjection::class, $id);

        self::assertNull($result);
    }

    public function testFindByReturnsMatchingProjections(): void
    {
        $projection1 = new TestProjection($id1 = Id::fromString('11111111-1111-1111-1111-111111111111'));
        $projection2 = new TestProjection(Id::fromString('22222222-2222-2222-2222-222222222222'));

        $this->testRepository->addProjection($projection1);
        $this->testRepository->addProjection($projection2);

        $results = $this->manager->findBy(TestProjection::class, ['id' => $id1->toBinary()]);

        self::assertCount(1, $results);
        self::assertSame($projection1, $results[0]);
    }

    public function testFindOneByReturnsSingleProjection(): void
    {
        $projection1 = new TestProjection($id1 = Id::fromString('33333333-3333-3333-3333-333333333333'));
        $projection2 = new TestProjection(Id::fromString('44444444-4444-4444-4444-444444444444'));

        $this->testRepository->addProjection($projection1);
        $this->testRepository->addProjection($projection2);

        $result = $this->manager->findOneBy(TestProjection::class, ['id' => $id1->toBinary()]);

        self::assertSame($projection1, $result);
    }

    public function testSaveCallsRepositorySave(): void
    {
        $projection = new TestProjection(Id::fromString('55555555-5555-5555-5555-555555555555'));

        $this->manager->save($projection);

        self::assertSame($projection, $this->testRepository->getLastSavedProjection());
    }
}
