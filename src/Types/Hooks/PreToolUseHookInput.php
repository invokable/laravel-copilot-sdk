<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

/**
 * Input for pre-tool-use hook.
 */
readonly class PreToolUseHookInput extends BaseHookInput
{
    /**
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  string  $toolName  Name of the tool to be executed
     * @param  mixed  $toolArgs  Arguments to be passed to the tool
     */
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
