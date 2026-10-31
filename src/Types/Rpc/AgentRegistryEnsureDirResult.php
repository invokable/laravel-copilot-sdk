<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * The resolved registry directory and any creation failure.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentRegistryEnsureDirResult implements Arrayable
{
    public function __construct(
        public string $path,
        public ?AgentRegistryError $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: Arr::string($data, 'path'),
            error: isset($data['error']) ? AgentRegistryError::fromArray($data['error']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'path' => $this->path,
            'error' => $this->error?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
