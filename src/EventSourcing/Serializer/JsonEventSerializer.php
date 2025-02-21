<?php

namespace PfaffKIT\Essa\EventSourcing\Serializer;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\Serializer\Normalizer\AggregateEventNormalizer;
use PfaffKIT\Essa\EventSourcing\Serializer\Normalizer\IdentityNormalizer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class JsonEventSerializer implements EventSerializer
{
    public const string DATE_TIME_FORMAT = 'Y-m-d\TH:i:s.uP';

    private SerializerInterface $serializer;

    public function __construct(array $encoders = [], array $normalizers = [])
    {
        $encoders = array_merge(
            [new JsonEncoder()],
            $encoders
        );

        $normalizers = array_merge(
            [
                new IdentityNormalizer(), new BackedEnumNormalizer(),
                new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => self::DATE_TIME_FORMAT]),
                new AggregateEventNormalizer(), new ArrayDenormalizer(),
                new PropertyNormalizer(null, null, new ReflectionExtractor()),
            ],
            $normalizers
        );

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function serialize(AggregateEvent $event): string
    {
        return $this->serializer->serialize($event, 'json');
    }

    public function deserialize(string $data, string $type): AggregateEvent
    {
        return $this->serializer->deserialize($data, $type, 'json');
    }

    public function normalize(AggregateEvent $event): array
    {
        return $this->serializer->normalize($event);
    }

    public function denormalize(array $data, string $type): AggregateEvent
    {
        return $this->serializer->denormalize($data, $type);
    }

    public function encode(array $data): string
    {
        return $this->serializer->encode($data, 'json');
    }

    public function decode(string $data): array
    {
        return $this->serializer->decode($data, 'json');
    }
}
