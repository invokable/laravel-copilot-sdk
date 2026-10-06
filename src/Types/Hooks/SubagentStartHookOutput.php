<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for the sub-agent start hook.
 */
readonly class SubagentStartHookOutput implements Arrayable
{
    /**
     * @param  ?string  $additionalContext  Context prepended to the sub-agent's initial prompt
     */
    public function __construct(
        public ?string $additionalContext = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            additionalContext: $data['additionalContext'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'additionalContext' => $this->additionalContext,
        ], fn ($value) => $value !== null);
    }
}
