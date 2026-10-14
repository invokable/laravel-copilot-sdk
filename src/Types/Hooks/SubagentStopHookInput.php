<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Support\Arr;

/**
 * Input for the hook fired after a sub-agent completes a turn.
 */
readonly class SubagentStopHookInput extends SubagentStartHookInput
{
    /**
     * @param  string  $sessionId  Parent runtime session ID
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  string  $transcriptPath  Path to the sub-agent transcript
     * @param  string  $agentName  Sub-agent name
     * @param  ?string  $agentDisplayName  Optional display name
     * @param  ?string  $agentDescription  Optional description
     * @param  ?string  $agentId  Sub-agent instance identifier
     * @param  ?string  $agentType  Sub-agent type, when available
     * @param  string  $stopReason  Why the sub-agent stopped
     * @param  string  $response  Last assistant message before any hook rewrite
     * @param  ?string  $parentAgentId  Immediate parent actor ID, when available
     */
    public function __construct(
        string $sessionId,
        int $timestamp,
        string $cwd,
        string $transcriptPath,
        string $agentName,
        ?string $agentDisplayName = null,
        ?string $agentDescription = null,
        ?string $agentId = null,
        ?string $agentType = null,
        public string $stopReason = 'end_turn',
        public string $response = '',
        ?string $parentAgentId = null,
    ) {
        parent::__construct(
            $sessionId,
            $timestamp,
            $cwd,
            $transcriptPath,
            $agentName,
            $agentDisplayName,
            $agentDescription,
            $agentId,
            $agentType,
            $parentAgentId,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            sessionId: Arr::string($data, 'sessionId', ''),
            timestamp: Arr::integer($data, 'timestamp', 0),
            cwd: Arr::string($data, 'cwd', ''),
            transcriptPath: Arr::string($data, 'transcriptPath', ''),
            agentName: Arr::string($data, 'agentName', ''),
            agentDisplayName: $data['agentDisplayName'] ?? null,
            agentDescription: $data['agentDescription'] ?? null,
            agentId: $data['agentId'] ?? null,
            agentType: $data['agentType'] ?? null,
            stopReason: Arr::string($data, 'stopReason', 'end_turn'),
            response: Arr::string($data, 'response', ''),
            parentAgentId: $data['parentAgentId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            ...parent::toArray(),
            'stopReason' => $this->stopReason,
            'response' => $this->response,
        ], fn ($value) => $value !== null);
    }
}
