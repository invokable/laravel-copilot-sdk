<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Subagent settings to apply to the current session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class UpdateSubagentSettingsRequest implements Arrayable
{
    /**
     * @param  SubagentSettings|null  $subagents  Subagent settings to apply, or null to clear the live session override
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
        ], fn ($v) => $v !== null);
    }
}
