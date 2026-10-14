<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Support\Arr;

/**
 * Input for pre-MCP-tool-call hook.
 *
 * Fires before an MCP tool call is dispatched to an MCP server.
 * Allows inspection and modification of tool call metadata.
 */
readonly class PreMcpToolCallHookInput extends BaseHookInput
{
    /**
     * @param  string  $sessionId  The runtime session ID of the session that triggered the hook
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $workingDirectory  Current working directory
     * @param  string  $serverName  Name of the MCP server
     * @param  string  $toolName  Name of the tool being called
     * @param  mixed  $arguments  Arguments to be passed to the tool
     * @param  ?string  $toolCallId  Optional tool call identifier
     * @param  ?array<string, mixed>  $_meta  Optional metadata associated with the tool call
     * @param  ?string  $agentId  Sub-agent actor ID, when available
     * @param  ?string  $agentType  Sub-agent type, when available
     * @param  ?string  $parentAgentId  Immediate parent actor ID, when available
     */
    public function __construct(
        string $sessionId,
        int $timestamp,
        public string $workingDirectory,
        public string $serverName,
        public string $toolName,
        public mixed $arguments,
        public ?string $toolCallId = null,
        public ?array $_meta = null,
        ?string $agentId = null,
        ?string $agentType = null,
        ?string $parentAgentId = null,
    ) {
        // Note: BaseHookInput expects 'cwd' but this hook uses 'workingDirectory'
        // We pass workingDirectory as cwd to the parent for consistency
        parent::__construct($sessionId, $timestamp, $workingDirectory, $agentId, $agentType, $parentAgentId);
    }

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            sessionId: $data['sessionId'] ?? '',
            timestamp: $data['timestamp'] ?? 0,
            workingDirectory: Arr::string($data, 'workingDirectory', ''),
            serverName: Arr::string($data, 'serverName', ''),
            toolName: Arr::string($data, 'toolName', ''),
            arguments: $data['arguments'] ?? null,
            toolCallId: $data['toolCallId'] ?? null,
            _meta: $data['_meta'] ?? null,
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
            'workingDirectory' => $this->workingDirectory,
            'serverName' => $this->serverName,
            'toolName' => $this->toolName,
            'arguments' => $this->arguments,
            'toolCallId' => $this->toolCallId,
            '_meta' => $this->_meta,
            'agentId' => $this->agentId,
            'agentType' => $this->agentType,
            'parentAgentId' => $this->parentAgentId,
        ], fn ($value) => $value !== null);
    }
}
