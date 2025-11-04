<?php

namespace PfaffKIT\Essa\EventSourcing\Serializer;

use PfaffKIT\Essa\EventSourcing\Projection\Projection;
use PfaffKIT\Essa\EventSourcing\Serializer\Normalizer\IdentityNormalizer;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Symfony\Component\Serializer\SerializerInterface;

class JsonProjectionSerializer implements ProjectionSerializer
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
                new IdentityNormalizer(),
                new BackedEnumNormalizer(),
                new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => self::DATE_TIME_FORMAT]),
                new ObjectNormalizer(
                    null,
                    null,
                    null,
                    new PropertyInfoExtractor(typeExtractors: [new PhpDocExtractor(), new ReflectionExtractor()]),
                ),
                new ArrayDenormalizer(),
            ],
            $normalizers
        );

        $this->serializer = new SymfonySerializer($normalizers, $encoders);
    }

    public function serialize(Projection $projection): string
    {
        return $this->serializer->serialize($projection, 'json');
    }

    public function deserialize(string $data, string $type): Projection
    {
        return $this->serializer->deserialize($data, $type, 'json');
    }

    public function normalize(Projection $projection): array
    {
        return $this->serializer->normalize($projection, 'json');
    }

    /**
     * @template T of Projection
     *
     * @param array<string, mixed> $data
     * @param class-string<T>      $type
     *
     * @return T
     */
    public function denormalize(array $data, string $type): Projection
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
