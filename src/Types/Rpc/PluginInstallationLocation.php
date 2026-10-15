<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Plugin install location without an absolute host path.
 *
 * @experimental
 */
readonly class PluginInstallationLocation implements Arrayable
{
    public function __construct(
        public string $displayLabel,
        public string $relativePath,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            displayLabel: $data['displayLabel'],
            relativePath: $data['relativePath'],
        );
    }

    public function toArray(): array
    {
        return [
            'displayLabel' => $this->displayLabel,
            'relativePath' => $this->relativePath,
        ];
    }
}
