<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\SpawnCwdValidationReason;

/** @experimental */
readonly class AgentsValidateSpawnCwdResult implements Arrayable
{
    public function __construct(
        public bool $ok,
        public SpawnCwdValidationReason|string|null $reason = null,
        public ?string $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $reason = isset($data['reason']) ? Arr::string($data, 'reason') : null;

        return new self(
            ok: Arr::boolean($data, 'ok'),
            reason: $reason === null ? null : (SpawnCwdValidationReason::tryFrom($reason) ?? $reason),
            message: isset($data['message']) ? Arr::string($data, 'message') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ok' => $this->ok,
            'reason' => $this->reason instanceof SpawnCwdValidationReason ? $this->reason->value : $this->reason,
            'message' => $this->message,
        ], static fn ($value): bool => $value !== null);
    }
}
