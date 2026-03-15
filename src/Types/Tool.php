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
    /**
     * @param  string  $name  Tool name
     * @param  ?string  $description  Tool description
     * @param  ?array  $parameters  Tool parameter schema
     * @param  Closure  $handler  Tool handler function
     * @param  bool  $overridesBuiltInTool  Whether this tool overrides a built-in tool with the same name
     * @param  bool  $skipPermission  Whether to skip permission prompt for this tool
     */
    public function __construct(
        public string $name,
        public ?string $description,
        public ?array $parameters,
        public Closure $handler,
        public bool $overridesBuiltInTool = false,
        public bool $skipPermission = false,
    ) {}

    /**
     * Define a new tool.
     */
    public static function define(
        string $name,
        ?string $description,
        ?array $parameters,
        Closure $handler,
        bool $overridesBuiltInTool = false,
        bool $skipPermission = false,
    ): array {
        return new self($name, $description, $parameters, $handler, $overridesBuiltInTool, $skipPermission)->toArray();
    }

    /**
     * Create from array.
     *
     * @param  array{name: string, description?: string, parameters?: array, handler: callable, overridesBuiltInTool?: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            parameters: $data['parameters'] ?? null,
            handler: $data['handler'],
            overridesBuiltInTool: $data['overridesBuiltInTool'] ?? false,
            skipPermission: $data['skipPermission'] ?? false,
        );
    }

    /**
     * Convert to array.
     *
     * @return array{name: string, description: string|null, parameters: array|null, handler: Closure, overridesBuiltInTool?: bool}
     */
    public function toArray(): array
    {
        $array = [
            'name' => $this->name,
            'description' => $this->description,
            'parameters' => $this->parameters,
            'handler' => $this->handler,
        ];

        if ($this->overridesBuiltInTool) {
            $array['overridesBuiltInTool'] = true;
        }

        if ($this->skipPermission) {
            $array['skipPermission'] = true;
        }

        return $array;
    }
}
