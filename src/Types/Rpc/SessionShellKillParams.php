<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for killing a shell process in a session.
 */
readonly class SessionShellKillParams implements Arrayable
{
    public function __construct(
        /**
         * Process identifier returned by shell.exec.
         */
        public string $processId,
        /**
         * Signal to send (default: SIGTERM).
         */
        public ?string $signal = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            processId: $data['processId'],
            signal: $data['signal'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'processId' => $this->processId,
            'signal' => $this->signal,
        ], fn ($v) => $v !== null);
    }
}
