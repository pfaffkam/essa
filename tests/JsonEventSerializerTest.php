<?php

namespace PfaffKIT\Essa\Tests;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\Serializer\EventSerializer;
use PfaffKIT\Essa\EventSourcing\Serializer\JsonEventSerializer;
use PfaffKIT\Essa\Shared\Id;
use PfaffKIT\Essa\Tests\mocks\TestAggregateEvent;
use PfaffKIT\Essa\Tests\mocks\TestDerivedAggregateEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonEventSerializer::class)]
class JsonEventSerializerTest extends TestCase
{
    private EventSerializer $serializer;

    public function setUp(): void
    {
        parent::setUp();

        $this->serializer = new JsonEventSerializer();
    }

    #[DataProvider('getEventData')]
    public function testSerialize(AggregateEvent $event): array
    {
        $serialized = $this->serializer->serialize($event);

        $this->assertIsString($serialized);

        return [
            'event' => $event,
            'serialized' => $serialized,
        ];
    }

    #[DataProvider('getEventData')]
    public function testDeserialize(AggregateEvent $event)
    {
        // a little hack to 'merge' behavior of "depends" attribute and dataProvider
        $stack = $this->testSerialize($event);

        $deserialized = $this->serializer->deserialize($stack['serialized'], $stack['event']::class);

        $this->assertInstanceOf($stack['event']::class, $deserialized);
        $this->assertEquals($stack['event'], $deserialized);
    }

    #[DataProvider('getEventData')]
    public function testNormalize(AggregateEvent $event, array $properNormalized): array
    {
        $normalized = $this->serializer->normalize($event);

        $this->assertIsArray($normalized);
        $this->assertEquals($properNormalized, $normalized);

        return [
            'event' => $event,
            'normalized' => $normalized,
        ];
    }

    #[DataProvider('getEventData')]
    public function testDenormalize(AggregateEvent $event, array $properNormalized)
    {
        $stack = $this->testNormalize($event, $properNormalized);

        $denormalized = $this->serializer->denormalize($stack['normalized'], $stack['event']::class);

        $this->assertInstanceOf($stack['event']::class, $denormalized);
        $this->assertEquals($stack['event'], $denormalized);
    }

    #[DataProvider('getEventData')]
    public function testEncode(AggregateEvent $event, array $properNormalized, string $properEncoded): array
    {
        $stack = $this->testNormalize($event, $properNormalized);

        $encoded = $this->serializer->encode($stack['normalized']);

        $this->assertIsString($encoded);
        $this->assertEquals($properEncoded, $encoded);

        return [
            'normalized' => $stack['normalized'],
            'encoded' => $encoded,
        ];
    }

    #[DataProvider('getEventData')]
    public function testDecode(AggregateEvent $event, array $properNormalized, string $properEncoded): array
    {
        $stack = $this->testEncode($event, $properNormalized, $properEncoded);

        $decoded = $this->serializer->decode($stack['encoded']);

        $this->assertIsArray($decoded);
        $this->assertEquals($stack['normalized'], $decoded);
        $this->assertEquals($properNormalized, $decoded);

        return $stack;
    }

    public static function getEventData(): array
    {
        return [
            'test_event' => [
                new TestAggregateEvent( // object
                    $id = Id::new(),
                    new \DateTimeImmutable('2025-02-03T02:40:10.369998+01:00'),
                    'sample string event data',
                ),
                $o = [ // normalized
                    '_id' => (string) $id,
                    '_name' => 'test_event',
                    '_timestamp' => '2025-02-03T02:40:10.369998+01:00',
                    '_payload' => [
                        'stringData' => 'sample string event data',
                    ],
                ],
                json_encode($o),
            ],
            'test_derived_event' => [
                $ev = new TestDerivedAggregateEvent(
                    'sample string derived event data'
                ),
                $o = [// normalized
                    '_id' => (string) $ev->eventId,
                    '_name' => 'test_derived_event',
                    '_timestamp' => $ev->timestamp->format(JsonEventSerializer::DATE_TIME_FORMAT),
                    '_payload' => [
                        'stringData' => 'sample string derived event data',
                    ],
                ],
                json_encode($o),
            ],
        ];
    }
}
