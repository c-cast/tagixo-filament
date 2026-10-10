<?php

namespace Tagixo\Filament\Capabilities;

use Filament\Panel;
use Tagixo\Filament\TagixoFilamentPlugin;

/**
 * Something the panel gains because a package is installed, and that is not a
 * record type — those are resources, and the registry already answers for them.
 *
 * A capability decides for itself whether it applies: `available()` asks the
 * container, not the autoloader, so a package present on disk but not booted (or
 * deliberately switched off) does not turn the feature on.
 */
interface Capability
{
    public function id(): string;

    public function available(): bool;

    /**
     * Applied while the panel registers the plugin, which is inside the boot of
     * the panel provider.
     */
    public function apply(Panel $panel, TagixoFilamentPlugin $plugin): void;
}
