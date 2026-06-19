<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SubagentSettingsEntryContextTier;

/**
 * Subagent model, reasoning effort, and context tier settings.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SubagentSettingsEntry implements Arrayable
{
    /**
     * @param  SubagentSettingsEntryContextTier|null  $contextTier  Context tier override for matching subagents
     * @param  ?string  $effortLevel  Reasoning effort override for matching subagents
     * @param  ?string  $model  Model override for matching subagents
     */
    public function __construct(
        public ?SubagentSettingsEntryContextTier $contextTier = null,
        public ?string $effortLevel = null,
        public ?string $model = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contextTier: isset($data['contextTier']) ? SubagentSettingsEntryContextTier::from($data['contextTier']) : null,
            effortLevel: $data['effortLevel'] ?? null,
            model: $data['model'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'contextTier' => $this->contextTier?->value,
            'effortLevel' => $this->effortLevel,
            'model' => $this->model,
        ], fn ($v) => $v !== null);
    }
}
