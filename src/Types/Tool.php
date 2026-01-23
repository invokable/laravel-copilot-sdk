<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Tool definition.
 */
readonly class Tool implements Arrayable
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?array $parameters,
        public Closure $handler,
    ) {}

    /**
     * Define a new tool.
     */
    public static function define(
        string $name,
        ?string $description,
        ?array $parameters,
        Closure $handler,
    ): array {
        return new self($name, $description, $parameters, $handler)->toArray();
    }

    /**
     * Create from array.
     *
     * @param  array{name: string, description?: string, parameters?: array, handler: callable}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            parameters: $data['parameters'] ?? null,
            handler: $data['handler'],
        );
    }

    /**
     * Convert to array.
     *
     * @return array{state: string, terms: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'parameters' => $this->parameters,
            'handler' => $this->handler,
        ];
    }
}
