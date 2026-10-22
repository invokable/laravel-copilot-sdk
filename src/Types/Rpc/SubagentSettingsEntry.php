<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AgentModelPolicy;
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
     * @param  ?bool  $autoInvoke  Whether this agent's runtime-defined proactive invocation prompting is enabled, if supported.
     * @param  AgentModelPolicy|string|null  $modelPolicy  Whether the configured model strategy is preferred or required
     * @param  ?string  $providerId  Provider identifier for the configured model override
     */
    public function __construct(
        public ?SubagentSettingsEntryContextTier $contextTier = null,
        public ?string $effortLevel = null,
        public ?string $model = null,
        public ?bool $autoInvoke = null,
        public AgentModelPolicy|string|null $modelPolicy = null,
        public ?string $providerId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contextTier: isset($data['contextTier']) ? SubagentSettingsEntryContextTier::from($data['contextTier']) : null,
            effortLevel: $data['effortLevel'] ?? null,
            model: $data['model'] ?? null,
            autoInvoke: $data['autoInvoke'] ?? null,
            modelPolicy: isset($data['modelPolicy']) && is_string($data['modelPolicy'])
                ? (AgentModelPolicy::tryFrom($data['modelPolicy']) ?? $data['modelPolicy'])
                : null,
            providerId: $data['providerId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'contextTier' => $this->contextTier?->value,
            'effortLevel' => $this->effortLevel,
            'model' => $this->model,
            'autoInvoke' => $this->autoInvoke,
            'modelPolicy' => $this->modelPolicy instanceof AgentModelPolicy ? $this->modelPolicy->value : $this->modelPolicy,
            'providerId' => $this->providerId,
        ], fn ($v) => $v !== null);
    }
}
