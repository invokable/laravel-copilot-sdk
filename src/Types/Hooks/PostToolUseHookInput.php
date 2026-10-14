<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Support\Arr;
use Revolution\Copilot\Types\ToolResultObject;

/**
 * Input for post-tool-use hook.
 */
readonly class PostToolUseHookInput extends BaseHookInput
{
    /**
     * @param  string  $sessionId  The runtime session ID of the session that triggered the hook
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  string  $toolName  Name of the tool that was executed
     * @param  mixed  $toolArgs  Arguments passed to the tool
     * @param  ToolResultObject|array  $toolResult  Result returned by the tool
     * @param  ?string  $agentId  Sub-agent actor ID, when available
     * @param  ?string  $agentType  Sub-agent type, when available
     * @param  ?string  $parentAgentId  Immediate parent actor ID, when available
     */
    public function __construct(
        string $sessionId,
        int $timestamp,
        string $cwd,
        public string $toolName,
        public mixed $toolArgs,
        public ToolResultObject|array $toolResult,
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
        $toolResult = $data['toolResult'] ?? [];
        if (is_array($toolResult)) {
            $toolResult = ToolResultObject::fromArray($toolResult);
        }

        return new static(
            sessionId: $data['sessionId'] ?? '',
            timestamp: $data['timestamp'] ?? 0,
            cwd: $data['cwd'] ?? '',
            toolName: Arr::string($data, 'toolName', ''),
            toolArgs: $data['toolArgs'] ?? null,
            toolResult: $toolResult,
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
        $toolResult = $this->toolResult instanceof ToolResultObject
            ? $this->toolResult->toArray()
            : $this->toolResult;

        return [
            ...parent::toArray(),
            'toolName' => $this->toolName,
            'toolArgs' => $this->toolArgs,
            'toolResult' => $toolResult,
        ];
    }
}
