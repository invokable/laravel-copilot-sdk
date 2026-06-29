<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Canvas action that the agent or host can invoke.
 *
 * To discover the input schema for a particular action, call the list_canvas_capabilities tool.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class CanvasAction implements Arrayable
{
    /**
     * @param  string  $name  Action name exposed by the canvas provider.
     * @param  string|null  $description  Description of the action.
     * @param  mixed  $inputSchema  JSON Schema for the action input.
     */
    public function __construct(
        public string $name,
        public ?string $description = null,
        public mixed $inputSchema = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            description: $data['description'] ?? null,
            inputSchema: $data['inputSchema'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'inputSchema' => $this->inputSchema,
        ], fn ($value) => $value !== null);
    }
}
