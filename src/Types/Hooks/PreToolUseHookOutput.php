<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for pre-tool-use hook.
 */
readonly class PreToolUseHookOutput implements Arrayable
{
    /**
     * @param  ?string  $permissionDecision  Permission decision: "allow", "deny", or "ask"
     * @param  ?string  $permissionDecisionReason  Reason for the permission decision
     * @param  mixed  $modifiedArgs  Modified arguments for the tool
     * @param  ?string  $additionalContext  Additional context to provide to the agent
     * @param  ?bool  $suppressOutput  Whether to suppress output
     */
    public function __construct(
        public ?string $permissionDecision = null,
        public ?string $permissionDecisionReason = null,
        public mixed $modifiedArgs = null,
        public ?string $additionalContext = null,
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
