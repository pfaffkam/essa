<?php

namespace PfaffKIT\Essa\IntegrationEvent;

use PfaffKIT\Essa\EventSourcing\Serializer\Normalizer\IdentityNormalizer;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\Serializer as SymfonyMessengerSerializer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

/**
 * Integration event adapter to use in AMQP message brokers.
 */
class JsonIntegrationEventSerializer implements IntegrationEventSerializer
{
    public const string DATE_TIME_FORMAT = 'Y-m-d\TH:i:s.uP';

    private SymfonyMessengerSerializer $innerSerializer;

    public function __construct(array $encoders = [], array $normalizers = [])
    {
        $encoders = array_merge(
            [new JsonEncoder()],
            $encoders,
        );

        $normalizers = array_merge(
            [
                new IdentityNormalizer(), new BackedEnumNormalizer(),
                new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => self::DATE_TIME_FORMAT]),
                new ArrayDenormalizer(),
                new PropertyNormalizer(null, null, new ReflectionExtractor()),
            ],
            $normalizers
        );

        $symfonySerializer = new SymfonySerializer($normalizers, $encoders);

        $this->innerSerializer = new SymfonyMessengerSerializer($symfonySerializer);
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        return $this->innerSerializer->decode($encodedEnvelope);
    }

    public function encode(Envelope $envelope): array
    {
        return $this->innerSerializer->encode($envelope);
    }
}
