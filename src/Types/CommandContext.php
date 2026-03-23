<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Context passed to a command handler when a command is executed.
 */
readonly class CommandContext implements Arrayable
{
    /**
     * @param  string  $sessionId  Session ID where the command was invoked
     * @param  string  $command  The full command text (e.g. "/deploy production")
     * @param  string  $commandName  Command name without leading /
     * @param  string  $args  Raw argument string after the command name
     */
    public function __construct(
        public string $sessionId,
        public string $command,
        public string $commandName,
        public string $args,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['sessionId'],
            command: $data['command'],
            commandName: $data['commandName'],
            args: $data['args'],
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'command' => $this->command,
            'commandName' => $this->commandName,
            'args' => $this->args,
        ];
    }
}
