<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Configured per-agent subagent overrides, or null to clear the live session override.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SubagentSettings implements Arrayable
{
    /**
     * @param  array<string, SubagentSettingsEntry>|null  $agents  Per-agent settings keyed by subagent agent_type
     * @param  ?array<string>  $disabledSubagents  Names of subagents the user has turned off
     * @param  ?int  $maxConcurrency  Maximum number of subagents that can run concurrently; applies to usage-based billing users only
     * @param  ?int  $maxDepth  Maximum subagent nesting depth; applies to usage-based billing users only
     */
    public function __construct(
        public ?array $agents = null,
        public ?array $disabledSubagents = null,
        public ?int $maxConcurrency = null,
        public ?int $maxDepth = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $agents = null;
        if (isset($data['agents']) && is_array($data['agents'])) {
            $agents = array_map(
                fn (array $entry) => SubagentSettingsEntry::fromArray($entry),
                $data['agents'],
            );
        }

        return new self(
            agents: $agents,
            disabledSubagents: $data['disabledSubagents'] ?? null,
            maxConcurrency: isset($data['maxConcurrency']) ? (int) $data['maxConcurrency'] : null,
            maxDepth: isset($data['maxDepth']) ? (int) $data['maxDepth'] : null,
        );
    }

    public function toArray(): array
    {
        $agents = $this->agents !== null
            ? array_map(fn (SubagentSettingsEntry $e) => $e->toArray(), $this->agents)
            : null;

        return array_filter([
            'agents' => $agents,
            'disabledSubagents' => $this->disabledSubagents,
            'maxConcurrency' => $this->maxConcurrency,
            'maxDepth' => $this->maxDepth,
        ], fn ($v) => $v !== null);
    }
}
