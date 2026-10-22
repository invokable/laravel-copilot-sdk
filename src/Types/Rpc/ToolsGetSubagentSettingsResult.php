<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Current configured subagent settings for the session, before resolving defaults or inheritance.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ToolsGetSubagentSettingsResult implements Arrayable
{
    /**
     * @param  SubagentSettings|null  $subagents  Current subagent settings, or null when no settings block exists
     */
    public function __construct(
        public ?SubagentSettings $subagents = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            subagents: isset($data['subagents']) ? SubagentSettings::fromArray($data['subagents']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'subagents' => $this->subagents?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
