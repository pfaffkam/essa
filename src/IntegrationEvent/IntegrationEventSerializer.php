<?php

namespace PfaffKIT\Essa\IntegrationEvent;

use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface as SymfonyMessengerSerializerInterface;

interface IntegrationEventSerializer extends SymfonyMessengerSerializerInterface {}
