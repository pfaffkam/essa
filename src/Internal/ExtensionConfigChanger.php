<?php

namespace PfaffKIT\Essa\Internal;

class ExtensionConfigChanger
{
    public function __construct(
        private array $config,
    ) {}

    public function get(string $key): string
    {
        return $this->config[$key];
    }

    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    public function result(): array
    {
        return $this->config;
    }
}
