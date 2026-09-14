<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A JSON Schema output contract. OpenAI receives the name, description, schema and strict
 * setting; Anthropic receives the schema in output_config.format and always uses its native
 * strict enforcement.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class JsonSchemaResponseFormat implements Arrayable
{
    /**
     * @param  string  $name  Name of the output schema, subject to the provider's naming restrictions.
     * @param  mixed  $schema  JSON Schema passed unchanged to the inference provider.
     * @param  ?string  $description  Optional description passed to OpenAI providers.
     * @param  ?bool  $strict  Optional strict enforcement setting for OpenAI providers.
     */
    public function __construct(
        public string $name,
        public mixed $schema,
        public ?string $description = null,
        public ?bool $strict = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            schema: $data['schema'] ?? null,
            description: $data['description'] ?? null,
            strict: $data['strict'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'schema' => $this->schema,
            'description' => $this->description,
            'strict' => $this->strict,
        ], fn ($v) => $v !== null);
    }
}
