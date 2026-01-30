<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

/**
 * Input for pre-tool-use hook.
 */
readonly class PreToolUseHookInput extends BaseHookInput
{
    public function __construct(
        int $timestamp,
        string $cwd,
        public string $toolName,
        public mixed $toolArgs,
    ) {
        parent::__construct($timestamp, $cwd);
    }

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            timestamp: $data['timestamp'] ?? 0,
            cwd: $data['cwd'] ?? '',
            toolName: $data['toolName'] ?? '',
            toolArgs: $data['toolArgs'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'toolName' => $this->toolName,
            'toolArgs' => $this->toolArgs,
        ];
    }
}
