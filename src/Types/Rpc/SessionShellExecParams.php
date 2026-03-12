<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for executing a shell command in a session.
 */
readonly class SessionShellExecParams implements Arrayable
{
    public function __construct(
        /**
         * Shell command to execute.
         */
        public string $command,
        /**
         * Working directory (defaults to session working directory).
         */
        public ?string $cwd = null,
        /**
         * Timeout in milliseconds (default: 30000).
         */
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
