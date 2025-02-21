<?php

namespace PfaffKIT\Essa\Internal;

use Symfony\Component\Yaml\Yaml;

class ConfigFileModifier
{
    private array $yaml;

    public function __construct(
        private readonly string $configFilePath,
    ) {
        $this->yaml = Yaml::parse(file_get_contents($this->configFilePath));
    }

    public function extractExtensionConfig(string $extensionName): ?array
    {
        return $this->yaml['essa']['extensions'][$extensionName] ?? null;
    }

    public function setExtensionConfig(string $extensionName, array $extensionConfig): void
    {
        $this->yaml['essa']['extensions'][$extensionName] = $extensionConfig;
    }

    public function flush(): void
    {
        file_put_contents(
            $this->configFilePath,
            Yaml::dump(
                $this->yaml,
                999,
                flags: Yaml::DUMP_OBJECT_AS_MAP
            )
        );
    }
}
