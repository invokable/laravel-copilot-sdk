<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SandboxConfigSource;

/** @experimental */
readonly class SessionSandboxResetSessionChoicesRequest implements Arrayable
{
    public function __construct(
        public mixed $config,
        public SandboxConfigSource|string $configSource,
    ) {}

    public static function fromArray(array $data): self
    {
        $source = $data['configSource'];

        return new self(
            config: $data['config'],
            configSource: is_string($source) ? (SandboxConfigSource::tryFrom($source) ?? $source) : $source,
        );
    }

    public function toArray(): array
    {
        return [
            'config' => $this->config instanceof SandboxConfig ? $this->config->toArray() : $this->config,
            'configSource' => $this->configSource instanceof SandboxConfigSource ? $this->configSource->value : $this->configSource,
        ];
    }
}
