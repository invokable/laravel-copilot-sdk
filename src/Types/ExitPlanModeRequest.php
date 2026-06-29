<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to exit plan mode and continue with a selected action.
 */
readonly class ExitPlanModeRequest implements Arrayable
{
    /**
     * @param  string  $summary  Summary of the plan or proposed next step.
     * @param  list<string>  $actions  Available actions the user can select.
     * @param  string  $recommendedAction  The action recommended by the runtime.
     * @param  ?string  $planContent  Full plan content, when available.
     */
    public function __construct(
        public string $summary,
        public array $actions,
        public string $recommendedAction,
        public ?string $planContent = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            summary: Arr::string($data, 'summary', ''),
            actions: Arr::array($data, 'actions', []),
            recommendedAction: Arr::string($data, 'recommendedAction', ''),
            planContent: $data['planContent'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'summary' => $this->summary,
            'actions' => $this->actions,
            'recommendedAction' => $this->recommendedAction,
            'planContent' => $this->planContent,
        ], fn ($v) => $v !== null);
    }
}
