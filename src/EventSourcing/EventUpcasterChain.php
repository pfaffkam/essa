<?php

namespace PfaffKIT\Essa\EventSourcing;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class EventUpcasterChain
{
    /** @var EventUpcaster[] */
    private array $upcasters = [];

    public function __construct(
        #[AutowireIterator(EventUpcaster::class)]
        iterable $upcasters,
    ) {
        foreach ($upcasters as $upcaster) {
            $this->upcasters[] = $upcaster;
        }
    }

    public function upcast(array $data): array
    {
        $eventName = $data['_name'];

        if (!$eventName) {
            return $data;
        }

        $applied = true;
        while ($applied) {
            $applied = false;
            $currentVersion = $data['_version'];

            foreach ($this->upcasters as $upcaster) {
                if ($upcaster->supports($eventName, $currentVersion)) {
                    $data = $upcaster->upcast($data);
                    $applied = true;
                    break;
                }
            }
        }

        return $data;
    }
}
