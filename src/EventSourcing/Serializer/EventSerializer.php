<?php

namespace PfaffKIT\Essa\EventSourcing\Serializer;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;

interface EventSerializer
{
    /**
     * Fully serialize event from object to string.
     */
    public function serialize(AggregateEvent $event): string;
    /**
     * Fully deserialize event from string to object.
     */
    public function deserialize(string $data, string $type): AggregateEvent;

    /**
     * Normalize event to array.
     */
    public function normalize(AggregateEvent $event): array;
    /**
     * Denormalize event from array.
     */
    public function denormalize(array $data, string $type): AggregateEvent;

    /**
     * Encode array to string.
     */
    public function encode(array $data): string;
    /**
     * Decode string to array.
     */
    public function decode(string $data): array;
}
