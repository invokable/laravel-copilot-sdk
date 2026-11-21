<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class SandboxProxyCaCreateForConfigDirParams implements Arrayable
{
    public function __construct(
        public string $configDir,
        public SandboxConfig|array|null $sandboxConfig = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            configDir: Arr::string($data, 'configDir', ''),
            sandboxConfig: isset($data['sandboxConfig']) && is_array($data['sandboxConfig'])
                ? SandboxConfig::fromArray($data['sandboxConfig'])
                : ($data['sandboxConfig'] ?? null),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'configDir' => $this->configDir,
            'sandboxConfig' => $this->sandboxConfig instanceof SandboxConfig
                ? $this->sandboxConfig->toArray()
                : $this->sandboxConfig,
        ], static fn ($value): bool => $value !== null);
    }
}
