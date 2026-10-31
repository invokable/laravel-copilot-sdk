<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AutopilotEvaluateIdleContinuationOutcome;

/**
 * Result of evaluating the session-idle Autopilot continuation policy.
 *
 * @internal
 */
readonly class AutopilotEvaluateIdleContinuationResult implements Arrayable
{
    public function __construct(
        public AutopilotEvaluateIdleContinuationOutcome|string $outcome,
    ) {}

    public static function fromArray(array $data): self
    {
        $outcome = Arr::string($data, 'outcome', '');

        return new self(
            outcome: AutopilotEvaluateIdleContinuationOutcome::tryFrom($outcome) ?? $outcome,
        );
    }

    public function toArray(): array
    {
        return [
            'outcome' => $this->outcome instanceof AutopilotEvaluateIdleContinuationOutcome
                ? $this->outcome->value
                : $this->outcome,
        ];
    }
}
