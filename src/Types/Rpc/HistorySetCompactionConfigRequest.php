<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ReasoningEffort;

/**
 * Compaction settings for a session. Each call replaces the previous settings: an omitted field returns to its default.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistorySetCompactionConfigRequest implements Arrayable
{
    /**
     * @param  ?string  $model  Model that summarizes the conversation when it compacts. Ignored when blank.
     * @param  ?string  $prompt  Summarization instruction that replaces the built-in prompt. Ignored when blank.
     * @param  ReasoningEffort|string|null  $reasoningEffort  Reasoning effort for compaction requests, such as `none`, `minimal`, `low`, `medium`, `high`, `xhigh` or `max`.
     */
    public function __construct(
        public ?string $model = null,
        public ?string $prompt = null,
        public ReasoningEffort|string|null $reasoningEffort = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $reasoningEffort = $data['reasoningEffort'] ?? null;

        return new self(
            model: $data['model'] ?? null,
            prompt: $data['prompt'] ?? null,
            reasoningEffort: is_string($reasoningEffort)
                ? (ReasoningEffort::tryFrom($reasoningEffort) ?? $reasoningEffort)
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'model' => $this->model,
            'prompt' => $this->prompt,
            'reasoningEffort' => $this->reasoningEffort instanceof ReasoningEffort
                ? $this->reasoningEffort->value
                : $this->reasoningEffort,
        ], fn ($v) => $v !== null);
    }
}
