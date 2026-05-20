<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for setting the session filesystem provider.
 */
readonly class SessionFsSetProviderRequest implements Arrayable
{
    /**
     * @param  string  $initialCwd  Initial working directory for sessions
     * @param  string  $sessionStatePath  Path within each session's SessionFs where the runtime stores files
     * @param  string  $conventions  Path conventions: "windows" or "posix"
     * @param  ?SessionFsSetProviderCapabilities  $capabilities  Optional capabilities declared by the provider
     */
    public function __construct(
        public string $initialCwd,
        public string $sessionStatePath,
        public string $conventions = 'posix',
        public ?SessionFsSetProviderCapabilities $capabilities = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            initialCwd: $data['initialCwd'],
            sessionStatePath: $data['sessionStatePath'],
            conventions: $data['conventions'] ?? 'posix',
            capabilities: isset($data['capabilities']) ? SessionFsSetProviderCapabilities::fromArray($data['capabilities']) : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'initialCwd' => $this->initialCwd,
            'sessionStatePath' => $this->sessionStatePath,
            'conventions' => $this->conventions,
        ];

        if ($this->capabilities !== null) {
            $result['capabilities'] = $this->capabilities->toArray();
        }

        return $result;
    }
}
