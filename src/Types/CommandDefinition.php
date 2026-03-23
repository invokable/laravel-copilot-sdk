<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Definition of a slash command registered with the session.
 *
 * When the CLI is running with a TUI, registered commands appear as
 * `/commandName` for the user to invoke.
 */
readonly class CommandDefinition implements Arrayable
{
    /**
     * @param  string  $name  Command name (without leading /)
     * @param  Closure  $handler  Handler invoked when the command is executed.
     *                            Receives a CommandContext instance.
     * @param  ?string  $description  Human-readable description shown in command completion UI
     */
    public function __construct(
        public string $name,
        public Closure $handler,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            handler: $data['handler'],
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'handler' => $this->handler,
            'description' => $this->description,
        ], fn ($v) => $v !== null);
    }
}
