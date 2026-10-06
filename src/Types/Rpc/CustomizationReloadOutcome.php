<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class CustomizationReloadOutcome implements Arrayable
{
    public function __construct(
        public string $subsystem,
        public string $status,
        public ?string $detail = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            subsystem: $data['subsystem'] ?? '',
            status: $data['status'] ?? '',
            detail: $data['detail'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'subsystem' => $this->subsystem,
            'status' => $this->status,
            'detail' => $this->detail,
        ], static fn ($value) => $value !== null);
    }
}
