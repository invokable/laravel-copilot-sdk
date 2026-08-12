<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Options for one factory-scoped subagent call.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryAgentOptions implements Arrayable
{
    /**
     * @param  ?string  $label  Optional label distinguishing otherwise identical memoized agent calls.
     * @param  ?string  $model  Optional model identifier for the subagent.
     * @param  mixed  $schema  Optional JSON Schema for structured agent output.
     * @param  ?string  $reasoningEffort  Optional reasoning effort for the subagent.
     * @param  ?string  $contextTier  Optional context window tier for the subagent.
     * @param  ?string  $agent  Optional custom agent name for the subagent.
     */
    public function __construct(
        public ?string $label = null,
        public ?string $model = null,
        public mixed $schema = null,
        public ?string $reasoningEffort = null,
        public ?string $contextTier = null,
        public ?string $agent = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            label: $data['label'] ?? null,
            model: $data['model'] ?? null,
            schema: $data['schema'] ?? null,
            reasoningEffort: $data['reasoningEffort'] ?? null,
            contextTier: $data['contextTier'] ?? null,
            agent: $data['agent'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'label' => $this->label,
            'model' => $this->model,
            'schema' => $this->schema,
            'reasoningEffort' => $this->reasoningEffort,
            'contextTier' => $this->contextTier,
            'agent' => $this->agent,
        ], fn ($v) => $v !== null);
    }
}
