<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Runtime interpretation of the reasoning-summary experiment arm.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FeatureFlagReasoningSummariesArm implements Arrayable
{
    public function __construct(
        public bool $offByDefault,
        public bool $hintEnabled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            offByDefault: Arr::boolean($data, 'offByDefault'),
            hintEnabled: Arr::boolean($data, 'hintEnabled'),
        );
    }

    public function toArray(): array
    {
        return [
            'offByDefault' => $this->offByDefault,
            'hintEnabled' => $this->hintEnabled,
        ];
    }
}
