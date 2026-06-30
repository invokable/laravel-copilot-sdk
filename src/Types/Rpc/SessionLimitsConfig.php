<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional session limits.
 */
readonly class SessionLimitsConfig implements Arrayable
{
    /**
     * @param  ?float  $maxAiCredits  Maximum AI Credits allowed across the session's current accounting window.
     */
    public function __construct(
        public ?float $maxAiCredits = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            maxAiCredits: isset($data['maxAiCredits']) ? (float) $data['maxAiCredits'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'maxAiCredits' => $this->maxAiCredits,
        ], fn ($v) => $v !== null);
    }
}
