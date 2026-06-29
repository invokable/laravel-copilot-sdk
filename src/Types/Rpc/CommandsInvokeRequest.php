<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Slash command name and optional raw input string to invoke.
 */
readonly class CommandsInvokeRequest implements Arrayable
{
    /**
     * @param  string  $name  Command name. Leading slashes are stripped and the name is matched case-insensitively.
     * @param  ?string  $input  Raw input after the command name.
     */
    public function __construct(
        public string $name,
        public ?string $input = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            name: Arr::string($data, 'name', ''),
            input: $data['input'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'input' => $this->input,
        ], fn ($value) => $value !== null);
    }
}
