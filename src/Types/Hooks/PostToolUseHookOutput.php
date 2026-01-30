<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ToolResultObject;

/**
 * Output for post-tool-use hook.
 */
readonly class PostToolUseHookOutput implements Arrayable
{
    public function __construct(
        /**
         * Modified result to return.
         */
        public ToolResultObject|array|null $modifiedResult = null,
        /**
         * Additional context to provide to the agent.
         */
        public ?string $additionalContext = null,
        /**
         * Whether to suppress output.
         */
        public ?bool $suppressOutput = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        $modifiedResult = null;
        if (isset($data['modifiedResult'])) {
            $modifiedResult = $data['modifiedResult'] instanceof ToolResultObject
                ? $data['modifiedResult']
                : ToolResultObject::fromArray($data['modifiedResult']);
        }

        return new self(
            modifiedResult: $modifiedResult,
            additionalContext: $data['additionalContext'] ?? null,
            suppressOutput: $data['suppressOutput'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $modifiedResult = $this->modifiedResult instanceof ToolResultObject
            ? $this->modifiedResult->toArray()
            : $this->modifiedResult;

        return array_filter([
            'modifiedResult' => $modifiedResult,
            'additionalContext' => $this->additionalContext,
            'suppressOutput' => $this->suppressOutput,
        ], fn ($value) => $value !== null);
    }
}
