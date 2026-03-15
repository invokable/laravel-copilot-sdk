<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for killing a shell process in a session.
 */
readonly class SessionShellKillParams implements Arrayable
{
    /**
     * @param  string  $processId  Process identifier returned by shell.exec
     * @param  ?string  $signal  Signal to send (default: SIGTERM)
     */
    public function __construct(
        public string $processId,
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
