<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIPluginActivationWarning implements Arrayable
{
    public function __construct(
        public string $kind,
        public string $layer,
        public string $marketplace,
        public string $marketplaceDir,
        public string $message,
        public ?string $plugin = null,
        public ?string $expectedDir = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: Arr::string($data, 'kind', ''),
            layer: Arr::string($data, 'layer', ''),
            marketplace: Arr::string($data, 'marketplace', ''),
            marketplaceDir: Arr::string($data, 'marketplaceDir', ''),
            plugin: isset($data['plugin']) ? Arr::string($data, 'plugin') : null,
            expectedDir: isset($data['expectedDir']) ? Arr::string($data, 'expectedDir') : null,
            message: Arr::string($data, 'message', ''),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind,
            'layer' => $this->layer,
            'marketplace' => $this->marketplace,
            'marketplaceDir' => $this->marketplaceDir,
            'plugin' => $this->plugin,
            'expectedDir' => $this->expectedDir,
            'message' => $this->message,
        ], static fn ($value): bool => $value !== null);
    }
}
