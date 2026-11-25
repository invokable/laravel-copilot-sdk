<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class McpSecretsDeleteServerParams implements Arrayable
{
    public function __construct(
        public string $serverName,
        public ?string $configDir = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName'),
            configDir: $data['configDir'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverName' => $this->serverName,
            'configDir' => $this->configDir,
        ], static fn ($value) => $value !== null);
    }
}
