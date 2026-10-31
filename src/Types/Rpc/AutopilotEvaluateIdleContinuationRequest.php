<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for evaluating the session-idle Autopilot continuation policy.
 *
 * @internal
 */
readonly class AutopilotEvaluateIdleContinuationRequest implements Arrayable
{
    public static function fromArray(array $data): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return [];
    }
}
