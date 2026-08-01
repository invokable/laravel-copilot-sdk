<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Whether the session currently has an active self-paced schedule.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ScheduleHasSelfPacedResult implements Arrayable
{
    public function __construct(
        public bool $hasSelfPaced,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hasSelfPaced: (bool) ($data['hasSelfPaced'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'hasSelfPaced' => $this->hasSelfPaced,
        ];
    }
}
