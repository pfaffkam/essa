<?php

namespace PfaffKIT\Essa\Tests;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\Attribute\AsProjector;
use PfaffKIT\Essa\EventSourcing\Projection\Projection;
use PfaffKIT\Essa\EventSourcing\Projection\ProjectionHandlerLocator;
use PfaffKIT\Essa\EventSourcing\Projection\Projector;
use PfaffKIT\Essa\EventSourcing\Projection\ProjectorException;
use PfaffKIT\Essa\Shared\Id;
use PfaffKIT\Essa\Tests\mocks\TestProjection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;

#[CoversClass(ProjectionHandlerLocator::class)]
class ProjectionHandlerLocatorTest extends TestCase
{
    private HandlersLocatorInterface&MockObject $innerLocator;
    private ProjectionHandlerLocator $locator;

    protected function setUp(): void
    {
        $this->innerLocator = $this->createMock(HandlersLocatorInterface::class);
        $this->locator = new ProjectionHandlerLocator($this->innerLocator, $this->createMock(LoggerInterface::class));
    }

    public function testGetHandlersWithNonHandlerDescriptor(): void
    {
        $envelope = new Envelope(new \stdClass());
        $handler = new \stdClass();

        $this->innerLocator->method('getHandlers')
            ->with($envelope)
            ->willReturn([$handler]);

        $result = iterator_to_array($this->locator->getHandlers($envelope));
        $this->assertCount(0, $result);
    }

    public function testGetHandlersWithNonProjectorHandler(): void
    {
        $envelope = new Envelope(new \stdClass());
        $handler = $this->createHandlerDescriptor(function () {}, 'NonProjectorHandler::__invoke');

        $this->innerLocator->method('getHandlers')
            ->willReturn([$handler]);

        $result = iterator_to_array($this->locator->getHandlers($envelope));
        $this->assertCount(0, $result);
    }

    public function testGetHandlersWithProjectorHandlerAndOneParameter(): void
    {
        $event = $this->createMock(AggregateEvent::class);
        $envelope = new Envelope($event);

        $handler = $this->createHandlerDescriptor(
            #[AsProjector]
            function (AggregateEvent $event) {},
            'TestProjector::__invoke'
        );

        $this->innerLocator->method('getHandlers')
            ->willReturn([$handler]);

        $result = iterator_to_array($this->locator->getHandlers($envelope));
        $this->assertCount(1, $result);
    }

    public function testGetHandlersWithProjectorHandlerAndTwoParameters(): void
    {
        $event = $this->createMock(AggregateEvent::class);
        $envelope = new Envelope($event);

        // Create a test projector that works with our test projection
        $projector = new class implements Projector {
            public function load(AggregateEvent $event): ?Projection
            {
                return new TestProjection(
                    Id::new()
                );
            }

            public function save(Projection $projection): void {}

            #[AsProjector]
            public function handle(AggregateEvent $event, TestProjection $projection): void
            {
                // This will only be called if the type is correct
            }
        };

        $handler = new HandlerDescriptor([$projector, 'handle']);
        $this->innerLocator->method('getHandlers')
            ->willReturn([$handler]);

        $result = iterator_to_array($this->locator->getHandlers($envelope));
        $this->assertCount(1, $result);
    }

    public function testGetHandlersWithProjectorHandlerAndIncorrectProjectionType(): void
    {
        $event = $this->createMock(AggregateEvent::class);
        $envelope = new Envelope($event);

        // Create a test projector with incorrect parameter type hint
        $projector = new class implements Projector {
            public function load(AggregateEvent $event): ?Projection
            {
                return new TestProjection(
                    Id::new()
                );
            }

            public function save(Projection $projection): void {}

            #[AsProjector]
            public function handle(AggregateEvent $event, \stdClass $invalidType): void {}
        };

        $handler = new HandlerDescriptor([$projector, 'handle']);
        $this->innerLocator->method('getHandlers')
            ->willReturn([$handler]);

        $this->expectException(ProjectorException::class);
        $this->expectExceptionMessage('When projector have two parameters, second must be of Projection type.');

        iterator_to_array($this->locator->getHandlers($envelope));
    }

    public function testGetHandlersWithInvalidSecondParameter(): void
    {
        $event = $this->createMock(AggregateEvent::class);
        $envelope = new Envelope($event);

        $projector = new class {
            #[AsProjector]
            public function handle($event, \stdClass $invalid) {}
        };

        $handler = new HandlerDescriptor([$projector, 'handle']);

        $this->innerLocator->method('getHandlers')
            ->willReturn([$handler]);

        $this->expectException(ProjectorException::class);
        $this->expectExceptionMessage('When projector have two parameters, second must be of Projection type.');

        iterator_to_array($this->locator->getHandlers($envelope));
    }

    public function testGetHandlersWithInvalidParameterCount(): void
    {
        $event = $this->createMock(AggregateEvent::class);
        $envelope = new Envelope($event);

        $projector = new class {
            #[AsProjector]
            public function handle($a, $b, $c) {}
        };

        $handler = new HandlerDescriptor([$projector, 'handle']);

        $this->innerLocator->method('getHandlers')
            ->willReturn([$handler]);

        $this->expectException(ProjectorException::class);
        $this->expectExceptionMessage('Projector must have 1 parameter (only event) or 2 parameters (event, projection)');

        iterator_to_array($this->locator->getHandlers($envelope));
    }

    private function createHandlerDescriptor(callable $callable, string $name): HandlerDescriptor
    {
        $handler = new HandlerDescriptor($callable);

        // Use reflection to set the handler name
        $reflection = new \ReflectionClass($handler);
        $property = $reflection->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($handler, $name);

        return $handler;
    }
}
