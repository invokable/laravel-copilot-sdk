<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Revolution\Copilot\Types\ToolResultObject;

/**
 * Input for post-tool-use hook.
 */
readonly class PostToolUseHookInput extends BaseHookInput
{
    public function __construct(
        int $timestamp,
        string $cwd,
        public string $toolName,
        public mixed $toolArgs,
        public ToolResultObject|array $toolResult,
    ) {
        parent::__construct($timestamp, $cwd);
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
            timestamp: $data['timestamp'] ?? 0,
            cwd: $data['cwd'] ?? '',
            toolName: $data['toolName'] ?? '',
            toolArgs: $data['toolArgs'] ?? null,
            toolResult: $toolResult,
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
