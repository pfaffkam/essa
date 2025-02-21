<?php

namespace PfaffKIT\Essa\Internal;

/**
 * Every addon to this lib (as another composer lib) can implement this interface to allow user to configure the addon.
 *
 * It injects the interactive configuration process into 'bin/console essa:configure' symfony command.
 */
interface Configurator
{
    /**
     * Name of the extension which this configurator configures.
     */
    public static function getExtensionName(): string;

    /**
     * Should this configurator be executed?
     */
    public function shouldConfigure(): bool;

    /**
     * Configurator action.
     */
    public function configure(ConfiguratorLogWriter $log, ExtensionConfigChanger $configChanger): void;
}
