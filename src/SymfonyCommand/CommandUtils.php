<?php

namespace PfaffKIT\Essa\SymfonyCommand;

class CommandUtils
{
    public static function highlightFQCN(string $fqcn, string $pathColor = 'bright-blue', string $classColor = 'cyan', ?string $classOption = null): string
    {
        $parts = explode('\\', $fqcn);
        $namespace = implode('\\', array_slice($parts, 0, count($parts) - 1));
        $className = $parts[count($parts) - 1];

        return sprintf(
            "<fg=%s>%s\\\e</><fg=%s;options=%s>%s</>",
            $pathColor,
            $namespace,
            $classColor,
            $classOption ?: '',
            $className
        );
    }
}
