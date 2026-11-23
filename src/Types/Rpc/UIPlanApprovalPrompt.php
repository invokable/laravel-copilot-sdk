<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIPlanApprovalPrompt implements Arrayable
{
    /**
     * @param  array<UIExitPlanModeAction|string>  $actions
     */
    public function __construct(
        public string $requestId,
        public string $summary,
        public string $planContent,
        public array $actions,
        public UIExitPlanModeAction|string $recommendedAction,
        public ?string $model = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $actions = array_map(static function (mixed $action): UIExitPlanModeAction|string {
            return is_string($action) ? (UIExitPlanModeAction::tryFrom($action) ?? $action) : '';
        }, array_values(Arr::array($data, 'actions', [])));
        $recommendedAction = Arr::string($data, 'recommendedAction', '');

        return new self(
            requestId: Arr::string($data, 'requestId', ''),
            summary: Arr::string($data, 'summary', ''),
            planContent: Arr::string($data, 'planContent', ''),
            actions: $actions,
            recommendedAction: UIExitPlanModeAction::tryFrom($recommendedAction) ?? $recommendedAction,
            model: isset($data['model']) ? Arr::string($data, 'model') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'summary' => $this->summary,
            'planContent' => $this->planContent,
            'actions' => array_map(
                static fn (UIExitPlanModeAction|string $action): string => $action instanceof UIExitPlanModeAction ? $action->value : $action,
                $this->actions,
            ),
            'recommendedAction' => $this->recommendedAction instanceof UIExitPlanModeAction
                ? $this->recommendedAction->value
                : $this->recommendedAction,
            'model' => $this->model,
        ], static fn ($value): bool => $value !== null);
    }
}
