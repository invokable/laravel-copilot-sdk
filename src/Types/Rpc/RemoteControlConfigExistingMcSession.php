<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Existing Mission Control session to reattach to.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RemoteControlConfigExistingMcSession implements Arrayable
{
    public function __construct(
        public string $mcSessionId,
        public ?string $mcTaskId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mcSessionId: Arr::string($data, 'mcSessionId'),
            mcTaskId: $data['mcTaskId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mcSessionId' => $this->mcSessionId,
            'mcTaskId' => $this->mcTaskId,
        ], static fn ($value) => $value !== null);
    }
}
