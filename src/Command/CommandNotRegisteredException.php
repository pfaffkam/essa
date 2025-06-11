<?php

namespace PfaffKIT\Essa\Command;

use PfaffKIT\Essa\EventSourcing\Exception\EssaException;

class CommandNotRegisteredException extends \RuntimeException implements EssaException
{
    public function __construct(Command $command)
    {
        parent::__construct(sprintf("The command '%s' has no handler.", $command::class));
    }
}
