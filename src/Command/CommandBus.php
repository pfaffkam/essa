<?php

namespace PfaffKIT\Essa\Command;

interface CommandBus
{
    public function dispatch(Command $command): void;
}
