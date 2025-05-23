<?php

namespace PfaffKIT\Essa\Tests\DependencyInjection\Compiler;

use PfaffKIT\Essa\DependencyInjection\Compiler\ProjectorAutoconfigurationPass;
use PfaffKIT\Essa\EventSourcing\Projection\ProjectorAutoconfigurator;
use PfaffKIT\Essa\Tests\mocks\TestEvent;
use PfaffKIT\Essa\Tests\mocks\TestProjector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(ProjectorAutoconfigurationPass::class)]
#[UsesClass(ProjectorAutoconfigurator::class)]
class ProjectorAutoconfigurationPassTest extends TestCase
{
    public function testProcessWithProjector(): void
    {
        $container = new ContainerBuilder();

        // Register the ProjectorAutoconfigurator service
        $container->register(ProjectorAutoconfigurator::class, ProjectorAutoconfigurator::class)
            ->setPublic(true)
            ->setAutowired(true);

        // Register test projector with the 'projector' tag
        $container->setDefinition('test_projector', new Definition(TestProjector::class))
            ->addTag('projector')
            ->setPublic(true);

        // Create a mock logger to capture log messages
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $container->set('logger', $logger);

        // Run the pass
        $pass = new ProjectorAutoconfigurationPass();
        $pass->process($container);

        // Verify the test projector was registered as a message handler
        $def = $container->getDefinition('test_projector');
        $tags = $def->getTags();

        $this->assertArrayHasKey('messenger.message_handler', $tags, 'The test projector should be tagged as a message handler');

        // Check that the handler is registered for the correct event
        $handlerTags = $tags['messenger.message_handler'];
        $found = false;
        foreach ($handlerTags as $tag) {
            if (isset($tag['handles']) && TestEvent::class === $tag['handles']) {
                $found = true;
                $this->assertEquals('handleTestEvent', $tag['method']);
                $this->assertEquals('async', $tag['from_transport']);
                break;
            }
        }
        $this->assertTrue($found, 'No handler found for TestEvent');
    }

    public function testProcessWithoutProjectors(): void
    {
        $container = new ContainerBuilder();

        // Run the pass without any projectors
        $pass = new ProjectorAutoconfigurationPass();
        $pass->process($container);

        // The pass should not throw any exceptions
        $this->assertTrue(true);
    }

    public function testProcessWithInvalidProjector(): void
    {
        $container = new ContainerBuilder();

        // Register an invalid projector with a non-existent class
        $container->setDefinition('invalid_projector', new Definition('NonExistent\Class'))
            ->addTag('projector')
            ->setPublic(true);

        // This should not throw an exception
        $pass = new ProjectorAutoconfigurationPass();
        $pass->process($container);

        $this->assertTrue(true);
    }
}
