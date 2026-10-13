<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Server-discovered hook actions and partial-load diagnostics.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HooksDiscoverResult implements Arrayable
{
    /**
     * @param  DiscoveredHook[]  $hooks
     * @param  string[]  $warnings
     * @param  string[]  $errors
     */
    public function __construct(
        public array $hooks,
        public array $warnings = [],
        public array $errors = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hooks: array_map(
                fn (array $hook): DiscoveredHook => DiscoveredHook::fromArray($hook),
                $data['hooks'] ?? [],
            ),
            warnings: $data['warnings'] ?? [],
            errors: $data['errors'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'hooks' => array_map(fn (DiscoveredHook $hook): array => $hook->toArray(), $this->hooks),
            'warnings' => $this->warnings,
            'errors' => $this->errors,
        ];
    }
}
