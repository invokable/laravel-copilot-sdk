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
     */
    public function __construct(
        public ?string $label = null,
        public ?string $model = null,
        public mixed $schema = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            label: $data['label'] ?? null,
            model: $data['model'] ?? null,
            schema: $data['schema'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'label' => $this->label,
            'model' => $this->model,
            'schema' => $this->schema,
        ], fn ($v) => $v !== null);
    }
}
