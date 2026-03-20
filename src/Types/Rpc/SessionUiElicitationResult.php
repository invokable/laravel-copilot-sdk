<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ElicitationAction;

/**
 * Result of a UI elicitation response.
 */
readonly class SessionUiElicitationResult implements Arrayable
{
    /**
     * @param  ElicitationAction  $action  The user's response: accept (submitted), decline (rejected), or cancel (dismissed)
     * @param  ?array  $content  The form values submitted by the user (present when action is 'accept')
     */
    public function __construct(
        public ElicitationAction $action,
        public ?array $content = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            action: ElicitationAction::from($data['action']),
            content: $data['content'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'action' => $this->action->value,
            'content' => $this->content,
        ], fn ($v) => $v !== null);
    }
}
