<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Support\Arr;

/**
 * Input for session-start hook.
 */
readonly class SessionStartHookInput extends BaseHookInput
{
    /**
     * @param  string  $sessionId  The runtime session ID of the session that triggered the hook
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  string  $source  Source of the session: "startup", "resume", or "new"
     * @param  ?string  $initialPrompt  Initial prompt, if any
     * @param  ?string  $agentId  Sub-agent actor ID, when available
     * @param  ?string  $agentType  Sub-agent type, when available
     * @param  ?string  $parentAgentId  Immediate parent actor ID, when available
     */
    public function __construct(
        string $sessionId,
        int $timestamp,
        string $cwd,
        public string $source,
        public ?string $initialPrompt = null,
        ?string $agentId = null,
        ?string $agentType = null,
        ?string $parentAgentId = null,
    ) {
        parent::__construct($sessionId, $timestamp, $cwd, $agentId, $agentType, $parentAgentId);
    }

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            sessionId: $data['sessionId'] ?? '',
            timestamp: $data['timestamp'] ?? 0,
            cwd: $data['cwd'] ?? '',
            source: Arr::string($data, 'source', 'new'),
            initialPrompt: $data['initialPrompt'] ?? null,
            agentId: $data['agentId'] ?? null,
            agentType: $data['agentType'] ?? null,
            parentAgentId: $data['parentAgentId'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            ...parent::toArray(),
            'source' => $this->source,
            'initialPrompt' => $this->initialPrompt,
        ], fn ($value) => $value !== null);
    }
}
