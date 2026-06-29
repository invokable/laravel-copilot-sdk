<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Response to an exit-plan-mode request.
 */
readonly class ExitPlanModeResult implements Arrayable
{
    /**
     * @param  bool  $approved  Whether the user approved exiting plan mode.
     * @param  ?string  $selectedAction  Selected action, if the user chose one.
     * @param  ?string  $feedback  Optional feedback provided by the user.
     */
    public function __construct(
        public bool $approved,
        public ?string $selectedAction = null,
        public ?string $feedback = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            approved: Arr::boolean($data, 'approved', false),
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
        ], fn ($v) => $v !== null);
    }
}
