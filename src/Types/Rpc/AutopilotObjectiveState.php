<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AutopilotObjectiveStatus;

/**
 * Public, persistence-independent projection of an Autopilot objective.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AutopilotObjectiveState implements Arrayable
{
    /**
     * @param  AutopilotObjectiveStatus|string  $status  Current objective lifecycle status.
     * @param  string  $creditCountNanoAiu  Exact lifetime AI-credit consumption in nano-AIU.
     * @param  string|null  $pauseReason  Optional reason the objective is paused.
     * @param  string|null  $completionSummary  Optional summary recorded when the objective completed.
     * @param  AutopilotObjectiveCreditLimit|array|null  $creditLimit  Current per-window credit limit and consumption.
     */
    public function __construct(
        public int $id,
        public string $objective,
        public AutopilotObjectiveStatus|string $status,
        public int $turnCount,
        public string $creditCountNanoAiu,
        public ?string $pauseReason = null,
        public ?string $completionSummary = null,
        public AutopilotObjectiveCreditLimit|array|null $creditLimit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = Arr::string($data, 'status');
        $creditLimit = $data['creditLimit'] ?? null;

        return new self(
            id: Arr::integer($data, 'id'),
            objective: Arr::string($data, 'objective'),
            status: AutopilotObjectiveStatus::tryFrom($status) ?? $status,
            turnCount: Arr::integer($data, 'turnCount'),
            creditCountNanoAiu: Arr::string($data, 'creditCountNanoAiu'),
            pauseReason: isset($data['pauseReason']) ? Arr::string($data, 'pauseReason') : null,
            completionSummary: isset($data['completionSummary']) ? Arr::string($data, 'completionSummary') : null,
            creditLimit: $creditLimit === null
                ? null
                : ($creditLimit instanceof AutopilotObjectiveCreditLimit
                    ? $creditLimit
                    : AutopilotObjectiveCreditLimit::fromArray($creditLimit)),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'objective' => $this->objective,
            'status' => $this->status instanceof AutopilotObjectiveStatus ? $this->status->value : $this->status,
            'turnCount' => $this->turnCount,
            'pauseReason' => $this->pauseReason,
            'completionSummary' => $this->completionSummary,
            'creditCountNanoAiu' => $this->creditCountNanoAiu,
            'creditLimit' => $this->creditLimit instanceof AutopilotObjectiveCreditLimit
                ? $this->creditLimit->toArray()
                : $this->creditLimit,
        ], fn ($value) => $value !== null);
    }
}
