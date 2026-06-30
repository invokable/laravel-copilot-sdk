<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\SessionLimitsExhaustedResponseAction;

/**
 * The user's selected action for an exhausted session limit.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class UISessionLimitsExhaustedResponse implements Arrayable
{
    /**
     * @param  SessionLimitsExhaustedResponseAction|string  $action
     * @param  ?float  $additionalAiCredits  AI Credits to add to the current max when action is 'add'.
     * @param  ?float  $maxAiCredits  New absolute max AI Credits when action is 'set'.
     */
    public function __construct(
        public SessionLimitsExhaustedResponseAction|string $action,
        public ?float $additionalAiCredits = null,
        public ?float $maxAiCredits = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $action = $data['action'] ?? SessionLimitsExhaustedResponseAction::Cancel->value;
        if (is_string($action)) {
            $action = SessionLimitsExhaustedResponseAction::tryFrom($action) ?? $action;
        }

        return new self(
            action: $action,
            additionalAiCredits: isset($data['additionalAiCredits']) ? (float) $data['additionalAiCredits'] : null,
            maxAiCredits: isset($data['maxAiCredits']) ? (float) $data['maxAiCredits'] : null,
        );
    }

    public function toArray(): array
    {
        $action = $this->action instanceof SessionLimitsExhaustedResponseAction
            ? $this->action->value
            : $this->action;

        return array_filter([
            'action' => $action,
            'additionalAiCredits' => $this->additionalAiCredits,
            'maxAiCredits' => $this->maxAiCredits,
        ], fn ($v) => $v !== null);
    }
}
