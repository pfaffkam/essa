<?php

namespace PfaffKIT\Essa\EventSourcing\Serializer;

use PfaffKIT\Essa\EventSourcing\Projection;

interface ProjectionSerializer
{
    /**
     * Fully serialize projection from object to string.
     */
    public function serialize(Projection $projection): string;

    /**
     * Fully deserialize projection from string to object.
     *
     * @template T of Projection
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    public function deserialize(string $data, string $type): Projection;

    /**
     * Normalize projection to array.
     *
     * @return array<string, mixed>
     */
    public function normalize(Projection $projection): array;

    /**
     * Denormalize projection from array.
     *
     * @template T of Projection
     *
     * @param array<string, mixed> $data
     * @param class-string<T>      $type
     *
     * @return T
     */
    public function denormalize(array $data, string $type): Projection;

    /**
     * Encode array to string.
     *
     * @param array<string, mixed> $data
     */
    public function encode(array $data): string;

    /**
     * Decode string to array.
     *
     * @return array<string, mixed>
     */
    public function decode(string $data): array;
}
