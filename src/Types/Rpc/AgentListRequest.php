<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional controls for including built-in agents and authored prompts.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentListRequest implements Arrayable
{
    /**
     * @param  ?bool  $includeBuiltInAgents  Include configured built-in agents.
     * @param  ?bool  $includePrompt  Include authored base prompt text when available.
     */
    public function __construct(
        public ?bool $includeBuiltInAgents = null,
        public ?bool $includePrompt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            includeBuiltInAgents: $data['includeBuiltInAgents'] ?? null,
            includePrompt: $data['includePrompt'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'includeBuiltInAgents' => $this->includeBuiltInAgents,
            'includePrompt' => $this->includePrompt,
        ], fn ($value): bool => $value !== null);
    }
}
