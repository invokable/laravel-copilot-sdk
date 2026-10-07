<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Current account-specific availability for an Auto routing preference.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AutoTierStatus implements Arrayable
{
    public function __construct(
        public bool $enabled,
        public ?string $message = null,
        public ?string $reason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enabled: Arr::boolean($data, 'enabled'),
            message: $data['message'] ?? null,
            reason: $data['reason'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'message' => $this->message,
            'reason' => $this->reason,
        ], fn ($value) => $value !== null);
    }
}
