<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ElicitationAction;

/**
 * The elicitation response (accept with form values, decline, or cancel).
 */
readonly class UIElicitationResponse implements Arrayable
{
    /**
     * @param  ElicitationAction|string  $action  The user's response: accept (submitted), decline (rejected), or cancel (dismissed)
     * @param  ?array<string, string|int|float|bool|array<string>>  $content  The form values submitted by the user (present when action is 'accept')
     */
    public function __construct(
        public ElicitationAction|string $action,
        public ?array $content = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $action = $data['action'];

        return new self(
            action: ElicitationAction::tryFrom($action) ?? $action,
            content: $data['content'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'action' => $this->action instanceof ElicitationAction ? $this->action->value : $this->action,
            'content' => $this->content,
        ], fn ($v) => $v !== null);
    }
}
