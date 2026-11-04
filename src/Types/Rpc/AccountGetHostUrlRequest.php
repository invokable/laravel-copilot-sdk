<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Configured GitHub host names to resolve.
 */
readonly class AccountGetHostUrlRequest implements Arrayable
{
    public function __construct(
        public ?string $copilotGhHost = null,
        public ?string $ghHost = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            copilotGhHost: $data['copilotGhHost'] ?? null,
            ghHost: $data['ghHost'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'copilotGhHost' => $this->copilotGhHost,
            'ghHost' => $this->ghHost,
        ], fn ($value) => $value !== null);
    }
}
