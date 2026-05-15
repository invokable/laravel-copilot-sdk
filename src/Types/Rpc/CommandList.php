<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Slash commands available in the session, after applying any include/exclude filters.
 */
readonly class CommandList implements Arrayable
{
    /**
     * @param  SlashCommandInfo[]  $commands  Commands available in this session.
     */
    public function __construct(
        public array $commands,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            commands: array_map(
                fn (array $item) => SlashCommandInfo::fromArray($item),
                $data['commands'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'commands' => array_map(fn (SlashCommandInfo $c) => $c->toArray(), $this->commands),
        ];
    }
}
