<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderCapabilities;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderRequest;

/**
 * Client-level configuration for a custom session filesystem provider.
 *
 * Pass it as the `sessionFs` (or `session_fs`) client option. When it is set the client
 * registers it with the runtime via `sessionFs.setProvider` on `start()`, and every
 * session must supply a `createSessionFsProvider` factory.
 */
readonly class SessionFsConfig implements Arrayable
{
    /**
     * @param  string  $initialCwd  Initial working directory for sessions (the user's project directory)
     * @param  string  $sessionStatePath  Path within each session's SessionFs where the runtime stores session-scoped files
     * @param  string  $conventions  Path conventions used by the provider: "windows" or "posix"
     * @param  ?SessionFsSetProviderCapabilities  $capabilities  Optional capabilities (`sqlite`, `binary`) declared by the provider
     */
    public function __construct(
        public string $initialCwd,
        public string $sessionStatePath,
        public string $conventions,
        public ?SessionFsSetProviderCapabilities $capabilities = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $capabilities = $data['capabilities'] ?? null;
        if ($capabilities instanceof Arrayable) {
            $capabilities = $capabilities->toArray();
        }

        return new self(
            initialCwd: is_string($data['initialCwd'] ?? null) ? $data['initialCwd'] : '',
            sessionStatePath: is_string($data['sessionStatePath'] ?? null) ? $data['sessionStatePath'] : '',
            conventions: is_string($data['conventions'] ?? null) ? $data['conventions'] : '',
            capabilities: is_array($capabilities) ? SessionFsSetProviderCapabilities::fromArray($capabilities) : null,
        );
    }

    /**
     * Validate the configuration (same rules as the official SDK).
     *
     * @throws InvalidArgumentException
     */
    public function validate(): void
    {
        if ($this->initialCwd === '') {
            throw new InvalidArgumentException('sessionFs.initialCwd is required');
        }

        if ($this->sessionStatePath === '') {
            throw new InvalidArgumentException('sessionFs.sessionStatePath is required');
        }

        if ($this->conventions !== 'windows' && $this->conventions !== 'posix') {
            throw new InvalidArgumentException("sessionFs.conventions must be either 'windows' or 'posix'");
        }
    }

    /**
     * Whether the provider declared the SQLite capability.
     */
    public function supportsSqlite(): bool
    {
        return $this->capabilities?->sqlite === true;
    }

    /**
     * Whether the provider declared the binary capability.
     */
    public function supportsBinary(): bool
    {
        return $this->capabilities?->binary === true;
    }

    /**
     * Parameters for the `sessionFs.setProvider` request.
     */
    public function toSetProviderRequest(): SessionFsSetProviderRequest
    {
        $capabilities = $this->capabilities !== null && $this->capabilities->toArray() !== []
            ? $this->capabilities
            : null;

        return new SessionFsSetProviderRequest(
            initialCwd: $this->initialCwd,
            sessionStatePath: $this->sessionStatePath,
            conventions: $this->conventions,
            capabilities: $capabilities,
        );
    }

    public function toArray(): array
    {
        return $this->toSetProviderRequest()->toArray();
    }
}
