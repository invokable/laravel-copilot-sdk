<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Base interface for all hook inputs.
 */
readonly class BaseHookInput implements Arrayable
{
    /**
     * @param  string  $sessionId  Runtime session ID associated with the hook. Child tool hooks use the child session ID;
     *                             sub-agent lifecycle hooks use the parent session ID, matching invocation session ID.
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  ?string  $agentId  Sub-agent actor ID, when available
     * @param  ?string  $agentType  Sub-agent type, when available
     * @param  ?string  $parentAgentId  Immediate parent actor ID, when available
     */
    public function __construct(
        public string $sessionId,
        public int $timestamp,
        public string $cwd,
        public ?string $agentId = null,
        public ?string $agentType = null,
        public ?string $parentAgentId = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            sessionId: Arr::string($data, 'sessionId', ''),
            timestamp: Arr::integer($data, 'timestamp', 0),
            cwd: Arr::string($data, 'cwd', ''),
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
            'sessionId' => $this->sessionId,
            'timestamp' => $this->timestamp,
            'cwd' => $this->cwd,
            'agentId' => $this->agentId,
            'agentType' => $this->agentType,
            'parentAgentId' => $this->parentAgentId,
        ], fn ($value) => $value !== null);
    }
}
