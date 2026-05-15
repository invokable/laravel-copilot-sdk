<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional filters controlling which command sources to include in the listing.
 */
readonly class CommandsListRequest implements Arrayable
{
    /**
     * @param  ?bool  $includeBuiltins  Include runtime built-in commands.
     * @param  ?bool  $includeClientCommands  Include commands registered by protocol clients, including SDK clients and extensions.
     * @param  ?bool  $includeSkills  Include enabled user-invocable skills and commands.
     */
    public function __construct(
        public ?bool $includeBuiltins = null,
        public ?bool $includeClientCommands = null,
        public ?bool $includeSkills = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            includeBuiltins: $data['includeBuiltins'] ?? null,
            includeClientCommands: $data['includeClientCommands'] ?? null,
            includeSkills: $data['includeSkills'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'includeBuiltins' => $this->includeBuiltins,
            'includeClientCommands' => $this->includeClientCommands,
            'includeSkills' => $this->includeSkills,
        ], fn ($value) => $value !== null);
    }
}
