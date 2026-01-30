<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for pre-tool-use hook.
 */
readonly class PreToolUseHookOutput implements Arrayable
{
    public function __construct(
        /**
         * Permission decision: "allow", "deny", or "ask".
         */
        public ?string $permissionDecision = null,
        /**
         * Reason for the permission decision.
         */
        public ?string $permissionDecisionReason = null,
        /**
         * Modified arguments for the tool.
         */
        public mixed $modifiedArgs = null,
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
        return new self(
            permissionDecision: $data['permissionDecision'] ?? null,
            permissionDecisionReason: $data['permissionDecisionReason'] ?? null,
            modifiedArgs: $data['modifiedArgs'] ?? null,
            additionalContext: $data['additionalContext'] ?? null,
            suppressOutput: $data['suppressOutput'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'permissionDecision' => $this->permissionDecision,
            'permissionDecisionReason' => $this->permissionDecisionReason,
            'modifiedArgs' => $this->modifiedArgs,
            'additionalContext' => $this->additionalContext,
            'suppressOutput' => $this->suppressOutput,
        ], fn ($value) => $value !== null);
    }
}
