<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AbortReason;

/**
 * Parameters for aborting the current turn.
 */
readonly class AbortRequest implements Arrayable
{
    /**
     * @param  ?AbortReason  $reason  Finite reason code describing why the current turn was aborted
     */
    public function __construct(
        public ?AbortReason $reason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            reason: isset($data['reason']) ? AbortReason::from($data['reason']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'reason' => $this->reason?->value,
        ], fn ($value) => $value !== null);
    }
}
