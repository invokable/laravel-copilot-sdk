<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Automatic continuation policy for sessions in Autopilot mode.
 *
 * @experimental
 */
readonly class AutopilotOptions implements Arrayable
{
    /**
     * @param  ?bool  $continuationEnabled  Whether automatic continuation is enabled.
     * @param  ?int  $maxContinues  Maximum automatic continuations outside an active objective.
     */
    public function __construct(
        public ?bool $continuationEnabled = null,
        public ?int $maxContinues = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            continuationEnabled: $data['continuationEnabled'] ?? null,
            maxContinues: $data['maxContinues'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'continuationEnabled' => $this->continuationEnabled,
            'maxContinues' => $this->maxContinues,
        ], fn ($value) => $value !== null);
    }
}
