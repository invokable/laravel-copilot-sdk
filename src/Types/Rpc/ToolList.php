<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * List of available tools.
 */
readonly class ToolList implements Arrayable
{
    /**
     * @param  array<array{name: string, namespacedName?: string, description: string, parameters?: array, instructions?: string}>  $tools  List of available built-in tools with metadata
     */
    public function __construct(
        public array $tools,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tools: $data['tools'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'tools' => $this->tools,
        ];
    }
}
