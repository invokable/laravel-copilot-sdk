<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class McpSecretsSaveParams implements Arrayable
{
    public function __construct(
        public string $secretId,
        public string $value,
        public ?string $configDir = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            secretId: Arr::string($data, 'secretId'),
            value: Arr::string($data, 'value'),
            configDir: $data['configDir'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'secretId' => $this->secretId,
            'value' => $this->value,
            'configDir' => $this->configDir,
        ], static fn ($value) => $value !== null);
    }
}
