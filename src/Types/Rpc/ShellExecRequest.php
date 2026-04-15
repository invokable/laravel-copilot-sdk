<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for executing a shell command in a session.
 */
readonly class ShellExecRequest implements Arrayable
{
    /**
     * @param  string  $command  Shell command to execute
     * @param  ?string  $cwd  Working directory (defaults to session working directory)
     * @param  ?int  $timeout  Timeout in milliseconds (default: 30000)
     */
    public function __construct(
        public string $command,
        public ?string $cwd = null,
        public ?int $timeout = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            command: $data['command'],
            cwd: $data['cwd'] ?? null,
            timeout: $data['timeout'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'command' => $this->command,
            'cwd' => $this->cwd,
            'timeout' => $this->timeout,
        ], fn ($v) => $v !== null);
    }
}
