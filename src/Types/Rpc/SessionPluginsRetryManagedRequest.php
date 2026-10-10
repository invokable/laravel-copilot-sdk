<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Plugins to retry for the session's managed settings. */
readonly class SessionPluginsRetryManagedRequest implements Arrayable
{
    /** @param ?string[] $plugins */
    public function __construct(
        public ?array $plugins = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(plugins: $data['plugins'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter(['plugins' => $this->plugins], fn ($value) => $value !== null);
    }
}
