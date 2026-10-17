<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Request to reload session hooks, optionally deferring repository hooks. */
readonly class SessionsReloadPluginHooksRequest implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public ?bool $deferRepoHooks = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
            deferRepoHooks: $data['deferRepoHooks'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
            'deferRepoHooks' => $this->deferRepoHooks,
        ], fn ($value): bool => $value !== null);
    }
}
