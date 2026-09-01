<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Managed sandbox enforcement state for a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxEnforcementStatus implements Arrayable
{
    /**
     * @param  bool  $required  Whether managed policy requires an available sandbox backend.
     * @param  bool  $blocked  Whether an enforcement failure has permanently blocked the session.
     * @param  ?string  $reason  The first sandbox enforcement failure that blocked the session.
     */
    public function __construct(
        public bool $required,
        public bool $blocked,
        public ?string $reason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            required: Arr::boolean($data, 'required', false),
            blocked: Arr::boolean($data, 'blocked', false),
            reason: isset($data['reason']) ? Arr::string($data, 'reason') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'required' => $this->required,
            'blocked' => $this->blocked,
            'reason' => $this->reason,
        ], fn ($value) => $value !== null);
    }
}
