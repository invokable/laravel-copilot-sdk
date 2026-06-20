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
     * @param  ?Closure  $handler  Tool handler function. When omitted, the tool is declaration-only and
     *                             must be resolved by the consumer via pending external tool request RPCs.
     * @param  bool  $overridesBuiltInTool  Whether this tool overrides a built-in tool with the same name
     * @param  bool  $skipPermission  Whether to skip permission prompt for this tool
     * @param  ?string  $defer  Controls whether the tool may be deferred (loaded lazily via tool search) rather than always pre-loaded. When `"auto"`, the tool can be deferred and surfaced through tool search. When `"never"`, the tool is always pre-loaded. Optional; defaults to `"auto"`.
     */
    public function __construct(
        public string $name,
        public ?string $description,
        public ?array $parameters,
        public ?Closure $handler = null,
        public bool $overridesBuiltInTool = false,
        public bool $skipPermission = false,
        public ?string $defer = 'auto',
    ) {}

    /**
     * Define a new tool.
     */
    public static function define(
        string $name,
        ?string $description,
        ?array $parameters,
        ?Closure $handler = null,
        bool $overridesBuiltInTool = false,
        bool $skipPermission = false,
        ?string $defer = 'auto',
    ): array {
        return new self(
            name: $name,
            description: $description,
            parameters: $parameters,
            handler: $handler,
            overridesBuiltInTool: $overridesBuiltInTool,
            skipPermission: $skipPermission,
            defer: $defer,
        )->toArray();
    }

    /**
     * Create from array.
     *
     * @param  array{name: string, description?: string, parameters?: array, handler?: callable, overridesBuiltInTool?: bool, skipPermission?: bool, defer?: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            parameters: $data['parameters'] ?? null,
            handler: $data['handler'] ?? null,
            overridesBuiltInTool: $data['overridesBuiltInTool'] ?? false,
            skipPermission: $data['skipPermission'] ?? false,
            defer: $data['defer'] ?? 'auto',
        );
    }

    /**
     * Convert to array.
     *
     * @return array{name: string, description: string|null, parameters: array|null, handler: Closure, overridesBuiltInTool?: bool, skipPermission?: bool}
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

        if ($this->defer !== null) {
            $array['defer'] = $this->defer;
        }

        return $array;
    }
}
