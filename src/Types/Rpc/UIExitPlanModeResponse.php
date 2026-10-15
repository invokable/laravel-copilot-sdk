<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Exit plan mode response for a pending UI request.
 */
readonly class UIExitPlanModeResponse implements Arrayable
{
    public function __construct(
        public bool $approved,
        public ?string $selectedAction = null,
        public ?string $feedback = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            approved: Arr::boolean($data, 'approved'),
            selectedAction: $data['selectedAction'] ?? null,
            feedback: $data['feedback'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'approved' => $this->approved,
            'selectedAction' => $this->selectedAction,
            'feedback' => $this->feedback,
        ], fn ($value) => $value !== null);
    }
}
