<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Provider-native structured output format. JSON Schema is forwarded without rewriting or
 * validating the schema or the generated output.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ResponseFormat implements Arrayable
{
    public string $type;

    public function __construct(
        public JsonSchemaResponseFormat $jsonSchema,
    ) {
        $this->type = 'json_schema';
    }

    public static function fromArray(array $data): self
    {
        return new self(
            jsonSchema: JsonSchemaResponseFormat::fromArray($data['jsonSchema'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'jsonSchema' => $this->jsonSchema->toArray(),
            'type' => $this->type,
        ];
    }
}
