<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\TaskCompletionOutcome;

/**
 * Completion-review decision produced by the task-completion tool.
 *
 * @experimental
 */
readonly class TaskCompletionDecision implements Arrayable
{
    public function __construct(
        public TaskCompletionOutcome|string $outcome,
        public ?string $reason = null,
        public ?bool $reviewerDerived = null,
        public mixed $reviewerResultMeta = null,
        public ?int $objectiveId = null,
        public ?bool $completionRejectionBudgetExhausted = null,
        public ?int $completionEligibilityToken = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $outcome = $data['outcome'] ?? '';
        $outcome = is_string($outcome)
            ? TaskCompletionOutcome::tryFrom($outcome) ?? $outcome
            : $outcome;

        return new self(
            outcome: $outcome,
            reason: $data['reason'] ?? null,
            reviewerDerived: $data['reviewerDerived'] ?? null,
            reviewerResultMeta: $data['reviewerResultMeta'] ?? null,
            objectiveId: $data['objectiveId'] ?? null,
            completionRejectionBudgetExhausted: $data['completionRejectionBudgetExhausted'] ?? null,
            completionEligibilityToken: $data['completionEligibilityToken'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'outcome' => $this->outcome instanceof TaskCompletionOutcome ? $this->outcome->value : $this->outcome,
            'reason' => $this->reason,
            'reviewerDerived' => $this->reviewerDerived,
            'reviewerResultMeta' => $this->reviewerResultMeta,
            'objectiveId' => $this->objectiveId,
            'completionRejectionBudgetExhausted' => $this->completionRejectionBudgetExhausted,
            'completionEligibilityToken' => $this->completionEligibilityToken,
        ], fn ($value) => $value !== null);
    }
}
