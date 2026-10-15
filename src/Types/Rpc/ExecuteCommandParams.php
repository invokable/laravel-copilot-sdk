<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for synchronously executing a slash command.
 */
readonly class ExecuteCommandParams implements Arrayable
{
    public function __construct(
        public string $commandName,
        public string $args,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            commandName: Arr::string($data, 'commandName'),
            args: Arr::string($data, 'args'),
        );
    }

    public function toArray(): array
    {
        return [
            'commandName' => $this->commandName,
            'args' => $this->args,
        ];
    }
}
