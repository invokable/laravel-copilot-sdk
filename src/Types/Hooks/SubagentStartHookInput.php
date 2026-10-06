<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Support\Arr;

/**
 * Input for the hook fired before a sub-agent's first turn.
 */
readonly class SubagentStartHookInput extends BaseHookInput
{
    /**
     * @param  string  $sessionId  Parent runtime session ID
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  string  $transcriptPath  Path to the sub-agent transcript
     * @param  string  $agentName  Sub-agent name
     * @param  ?string  $agentDisplayName  Optional display name
     * @param  ?string  $agentDescription  Optional description
     */
    public function __construct(
        string $sessionId,
        int $timestamp,
        string $cwd,
        public string $transcriptPath,
        public string $agentName,
        public ?string $agentDisplayName = null,
        public ?string $agentDescription = null,
    ) {
        parent::__construct($sessionId, $timestamp, $cwd);
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
        );
    }

    public function toArray(): array
    {
        return array_filter([
            ...parent::toArray(),
            'transcriptPath' => $this->transcriptPath,
            'agentName' => $this->agentName,
            'agentDisplayName' => $this->agentDisplayName,
            'agentDescription' => $this->agentDescription,
        ], fn ($value) => $value !== null);
    }
}
