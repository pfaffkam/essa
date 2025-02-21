<?php

namespace PfaffKIT\Essa\EventSourcing\Serializer\Normalizer;

use PfaffKIT\Essa\Shared\Id;
use PfaffKIT\Essa\Shared\Identity;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IdentityNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return Id::fromString($data);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Identity::class === $type;
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return (string) $object;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Identity;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Identity::class => false,
        ];
    }
}
