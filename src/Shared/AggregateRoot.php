<?php

namespace PfaffKIT\Essa\Shared;

/**
 * Base class for **non-event-sourced** aggregate roots.
 */
abstract class AggregateRoot
{
    private array $domainEvents = [];
    private array $integrationEvents = [];

    final public function recordDomainEvent(DomainEvent $domainEvent): void
    {
        $this->domainEvents[] = $domainEvent;
    }

    final public function isDomainEventsEmpty(): bool
    {
        return empty($this->domainEvents);
    }

    /**
     * @return array<DomainEvent>
     */
    final public function pullDomainEvents(): array
    {
        $recordedEvents = $this->domainEvents;
        $this->domainEvents = [];

        return $recordedEvents;
    }

    final public function recordIntegrationEvent(IntegrationEvent $integrationEvent): void
    {
        $this->integrationEvents[] = $integrationEvent;
    }

    final public function isIntegrationEventsEmpty(): bool
    {
        return empty($this->integrationEvents);
    }

    /**
     * @return array<IntegrationEvent>
     */
    final public function pullIntegrationEvents(): array
    {
        $recordedEvents = $this->integrationEvents;
        $this->integrationEvents = [];

        return $recordedEvents;
    }
}
