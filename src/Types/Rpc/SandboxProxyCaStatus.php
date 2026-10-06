<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class SandboxProxyCaStatus implements Arrayable
{
    public function __construct(
        public string $state,
        public bool $canInstall = false,
        public ?string $detail = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            state: $data['state'] ?? '',
            canInstall: (bool) ($data['canInstall'] ?? false),
            detail: $data['detail'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'state' => $this->state,
            'canInstall' => $this->canInstall,
            'detail' => $this->detail,
        ], static fn ($value) => $value !== null);
    }
}
