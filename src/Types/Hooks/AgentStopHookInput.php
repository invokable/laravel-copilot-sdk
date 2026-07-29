<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

/**
 * Input for agent-stop hook.
 *
 * Fires for the top-level (main) agent when it reaches a natural terminal
 * stop — i.e. the agent has gone idle without a pending non-terminal tool
 * call and was not aborted or blocked by a rejected tool. (For sub-agents,
 * the runtime fires a separate sub-agent stop lifecycle.)
 */
readonly class AgentStopHookInput extends BaseHookInput
{
    /**
     * @param  string  $sessionId  The runtime session ID of the session that triggered the hook
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  ?string  $stopReason  Why the agent stopped (for example, "end_turn")
     * @param  ?string  $transcriptPath  Path to the on-disk session transcript, when available
     * @param  ?bool  $stopHookActive  True when this stop is a re-entry triggered by a previous agent-stop `block` decision
     */
    public function __construct(
        string $sessionId,
        int $timestamp,
        string $cwd,
        public ?string $stopReason = null,
        public ?string $transcriptPath = null,
        public ?bool $stopHookActive = null,
    ) {
        parent::__construct($sessionId, $timestamp, $cwd);
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
            stopReason: $data['stopReason'] ?? null,
            transcriptPath: $data['transcriptPath'] ?? null,
            stopHookActive: $data['stopHookActive'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            ...parent::toArray(),
            'stopReason' => $this->stopReason,
            'transcriptPath' => $this->transcriptPath,
            'stopHookActive' => $this->stopHookActive,
        ], fn ($value) => $value !== null);
    }
}
