<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SectionOverrideAction;

/**
 * Override operation for a single system prompt section.
 */
readonly class SectionOverride implements Arrayable
{
    /**
     * @param  SectionOverrideAction|string  $action  The operation to perform on this section
     * @param  ?string  $content  Content for the override. Optional for all actions.
     */
    public function __construct(
        public SectionOverrideAction|string $action,
        public ?string $content = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            action: SectionOverrideAction::tryFrom($data['action'] ?? '') ?? $data['action'],
            content: $data['content'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'action' => $this->action instanceof SectionOverrideAction ? $this->action->value : $this->action,
            'content' => $this->content,
        ], fn ($v) => $v !== null);
    }
}
