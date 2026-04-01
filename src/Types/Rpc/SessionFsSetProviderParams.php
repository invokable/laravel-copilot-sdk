<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for setting the session filesystem provider.
 */
readonly class SessionFsSetProviderParams implements Arrayable
{
    /**
     * @param  string  $initialCwd  Initial working directory for sessions
     * @param  string  $sessionStatePath  Path within each session's SessionFs where the runtime stores files
     * @param  string  $conventions  Path conventions: "windows" or "posix"
     */
    public function __construct(
        public string $initialCwd,
        public string $sessionStatePath,
        public string $conventions = 'posix',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            initialCwd: $data['initialCwd'],
            sessionStatePath: $data['sessionStatePath'],
            conventions: $data['conventions'] ?? 'posix',
        );
    }

    public function toArray(): array
    {
        return [
            'initialCwd' => $this->initialCwd,
            'sessionStatePath' => $this->sessionStatePath,
            'conventions' => $this->conventions,
        ];
    }
}
