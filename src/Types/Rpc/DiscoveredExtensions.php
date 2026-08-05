<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\DiscoveredExtensionMode;

/**
 * Extensions discovered from persisted Copilot home state.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class DiscoveredExtensions implements Arrayable
{
    public function __construct(
        public array $extensions,
        public DiscoveredExtensionMode|string $mode,
    ) {}

    public static function fromArray(array $data): self
    {
        $mode = $data['mode'] ?? '';

        return new self(
            extensions: array_map(
                fn (array $extension): DiscoveredExtension => DiscoveredExtension::fromArray($extension),
                $data['extensions'] ?? [],
            ),
            mode: DiscoveredExtensionMode::tryFrom($mode) ?? $mode,
        );
    }

    public function toArray(): array
    {
        return [
            'extensions' => array_map(
                fn (DiscoveredExtension $extension): array => $extension->toArray(),
                $this->extensions,
            ),
            'mode' => $this->mode instanceof DiscoveredExtensionMode ? $this->mode->value : $this->mode,
        ];
    }
}
