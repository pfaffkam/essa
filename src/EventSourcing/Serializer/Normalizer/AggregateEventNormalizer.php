<?php

namespace PfaffKIT\Essa\EventSourcing\Serializer\Normalizer;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AggregateEventNormalizer implements NormalizerInterface, NormalizerAwareInterface, DenormalizerInterface, DenormalizerAwareInterface
{
    private const string NORMALIZER_ALREADY_CALLED = 'AGGREGATE_EVENT_NORMALIZER_ALREADY_CALLED';
    private const string DENORMALIZER_ALREADY_CALLED = 'AGGREGATE_EVENT_DENORMALIZER_ALREADY_CALLED';

    private readonly NormalizerInterface $baseNormalizer;
    private readonly DenormalizerInterface $baseDenormalizer;

    /**
     * @param AggregateEvent $data
     *
     * @throws ExceptionInterface
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $context[self::NORMALIZER_ALREADY_CALLED] = true;
        $context[AbstractNormalizer::IGNORED_ATTRIBUTES] = ['eventId', 'name', 'timestamp'];

        $normalizedData = $this->baseNormalizer->normalize($data, 'json', $context);

        return [
            '_id' => (string) $data->eventId,
            '_name' => $data->getEventName(),
            '_timestamp' => $this->baseNormalizer->normalize($data->timestamp, 'json', $context),
            '_payload' => $normalizedData,
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::NORMALIZER_ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof AggregateEvent;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::DENORMALIZER_ALREADY_CALLED] = true;

        // flatten data
        $data['eventId'] = $data['_id'];
//        $data['name'] = $data['_name'];
        $data['timestamp'] = $data['_timestamp'];
        unset($data['_id'], $data['_name'], $data['_timestamp']);

        $data = array_merge($data, $data['_payload']);
        unset($data['_payload']);

        $reflection = new \ReflectionClass($type);
        $instance = $reflection->newInstanceWithoutConstructor();

        $this->baseDenormalizer->denormalize(
            $data,
            $type,
            'json',
            array_merge($context,
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $instance,
                    DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS,
                ])
        );

        return $instance;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return
            !isset($context[self::DENORMALIZER_ALREADY_CALLED])
            && is_subclass_of($type, AggregateEvent::class);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AggregateEvent::class => false,
        ];
    }

    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        $this->baseNormalizer = $normalizer;
    }

    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->baseDenormalizer = $denormalizer;
    }
}
