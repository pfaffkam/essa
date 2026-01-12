<?php

namespace PfaffKIT\Essa\EventSourcing\Serializer\Normalizer;

use PfaffKIT\Essa\Shared\EventTimestamp;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventTimestampNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return new EventTimestamp($data);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return EventTimestamp::class === $type;
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return $data->epoch;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof EventTimestamp;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            EventTimestamp::class => false,
        ];
    }
}
