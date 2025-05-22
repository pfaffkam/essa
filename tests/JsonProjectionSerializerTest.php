<?php

namespace PfaffKIT\Essa\Tests;

use PfaffKIT\Essa\EventSourcing\Projection;
use PfaffKIT\Essa\EventSourcing\Serializer\JsonProjectionSerializer;
use PfaffKIT\Essa\EventSourcing\Serializer\ProjectionSerializer;
use PfaffKIT\Essa\Shared\Id;
use PfaffKIT\Essa\Tests\mocks\TestProjection;
use PfaffKIT\Essa\Tests\mocks\TestProjectionNestedData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonProjectionSerializer::class)]
class JsonProjectionSerializerTest extends TestCase
{
    private ProjectionSerializer $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = new JsonProjectionSerializer();
    }

    #[DataProvider('getProjectionData')]
    public function testSerialize(Projection $projection, array $expectedNormalized): array
    {
        $serialized = $this->serializer->serialize($projection);

        $this->assertIsString($serialized);

        return [
            'projection' => $projection,
            'serialized' => $serialized,
        ];
    }

    #[DataProvider('getProjectionData')]
    public function testDeserialize(Projection $projection, array $expectedNormalized): void
    {
        $stack = $this->testSerialize($projection, $expectedNormalized);
        $deserialized = $this->serializer->deserialize($stack['serialized'], $stack['projection']::class);

        $this->assertInstanceOf($stack['projection']::class, $deserialized);

        // Normalize both objects and compare the arrays
        $normalizedOriginal = $this->serializer->normalize($stack['projection']);
        $normalizedDeserialized = $this->serializer->normalize($deserialized);

        $this->assertEquals($normalizedOriginal, $normalizedDeserialized);
    }

    #[DataProvider('getProjectionData')]
    public function testNormalize(Projection $projection, array $expectedNormalized): array
    {
        $normalized = $this->serializer->normalize($projection);

        // Verify the entire normalized structure matches expectations
        $this->assertEquals($expectedNormalized, $normalized);

        return [
            'projection' => $projection,
            'normalized' => $normalized,
        ];
    }

    #[DataProvider('getProjectionData')]
    public function testDenormalize(Projection $projection, array $expectedNormalized): void
    {
        $normalized = $this->serializer->normalize($projection);
        $denormalized = $this->serializer->denormalize($normalized, $projection::class);

        $this->assertInstanceOf($projection::class, $denormalized);

        // Normalize the denormalized object and compare with the original normalized data
        $denormalizedNormalized = $this->serializer->normalize($denormalized);
        $this->assertEquals($normalized, $denormalizedNormalized);
    }

    #[DataProvider('getProjectionData')]
    public function testEncode(Projection $projection, array $expectedNormalized): array
    {
        $stack = $this->testNormalize($projection, $expectedNormalized);
        $encoded = $this->serializer->encode($stack['normalized']);

        $this->assertIsString($encoded);

        return [
            'projection' => $projection,
            'normalized' => $stack['normalized'],
            'encoded' => $encoded,
        ];
    }

    #[DataProvider('getProjectionData')]
    public function testDecode(Projection $projection, array $expectedNormalized): void
    {
        $stack = $this->testEncode($projection, $expectedNormalized);
        $decoded = $this->serializer->decode($stack['encoded']);

        $this->assertIsArray($decoded);

        // Handle DateTime comparison in decoded data
        $expected = [];
        $actual = [];

        foreach ($expectedNormalized as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                // For DateTime objects, compare formatted strings
                $expected[$key] = $value->format(JsonProjectionSerializer::DATE_TIME_FORMAT);
                $actual[$key] = $decoded[$key];
            } else {
                $expected[$key] = $value;
                $actual[$key] = $decoded[$key];
            }
        }

        $this->assertEquals($expected, $actual);
    }

    public static function getProjectionData(): array
    {
        $projectionId = Id::new();
        $timestamp = new \DateTimeImmutable('2025-05-21T10:30:00.123456+02:00');
        $nestedTimestamp = new \DateTimeImmutable('2025-05-20T15:45:00.987654+02:00');
        $nestedData = new TestProjectionNestedData(
            nestedName: 'Nested Object',
            nestedValue: 100,
            nestedDate: $nestedTimestamp
        );

        return [
            'basic_projection' => [
                new TestProjection(
                    id: $projectionId,
                    name: 'Test Projection',
                    value: 42,
                    updatedAt: $timestamp,
                    tags: ['tag1', 'tag2'],
                    nestedData: $nestedData
                ),
                [
                    'id' => (string) $projectionId,
                    'name' => 'Test Projection',
                    'value' => 42,
                    'updatedAt' => $timestamp->format(JsonProjectionSerializer::DATE_TIME_FORMAT),
                    'tags' => ['tag1', 'tag2'],
                    'nestedData' => [
                        'nestedName' => 'Nested Object',
                        'nestedValue' => 100,
                        'nestedDate' => $nestedTimestamp->format(JsonProjectionSerializer::DATE_TIME_FORMAT),
                    ],
                ],
            ],
            'minimal_projection' => [
                new TestProjection(
                    id: $projectionId,
                    name: 'Minimal',
                    value: 0,
                    updatedAt: $timestamp
                ),
                [
                    'id' => (string) $projectionId,
                    'name' => 'Minimal',
                    'value' => 0,
                    'updatedAt' => $timestamp->format(JsonProjectionSerializer::DATE_TIME_FORMAT),
                    'tags' => [],
                    'nestedData' => null,
                ],
            ],
        ];
    }
}
