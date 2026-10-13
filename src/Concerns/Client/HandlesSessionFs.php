<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Client;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Contracts\SessionFsProvider;
use Revolution\Copilot\Session;
use Revolution\Copilot\Support\SessionFsProviderSupport;
use Revolution\Copilot\Types\Rpc\SessionFsAppendFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsExistsRequest;
use Revolution\Copilot\Types\Rpc\SessionFsMkdirRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirWithTypesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileBytesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsRenameRequest;
use Revolution\Copilot\Types\Rpc\SessionFsRmRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteExistsRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteQueryRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteTransactionRequest;
use Revolution\Copilot\Types\Rpc\SessionFsStatRequest;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileBytesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileRequest;
use Revolution\Copilot\Types\SessionFsConfig;
use RuntimeException;
use Throwable;

trait HandlesSessionFs
{
    private function sessionForFsRequest(array $params): Session
    {
        $sessionId = $params['sessionId'] ?? null;
        $session = is_string($sessionId) ? ($this->sessions[$sessionId] ?? null) : null;

        if (! $session instanceof Session) {
            throw new RuntimeException('Session not found for SessionFs request');
        }

        return $session;
    }

    private function handleSessionFsReadFile(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsReadFile(
            SessionFsReadFileRequest::fromArray($params),
        );
    }

    private function handleSessionFsReadFileBytes(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsReadFileBytes(
            SessionFsReadFileBytesRequest::fromArray($params),
        );
    }

    private function handleSessionFsWriteFile(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsWriteFile(
            SessionFsWriteFileRequest::fromArray($params),
        );
    }

    private function handleSessionFsWriteFileBytes(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsWriteFileBytes(
            SessionFsWriteFileBytesRequest::fromArray($params),
        );
    }

    private function handleSessionFsAppendFile(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsAppendFile(
            SessionFsAppendFileRequest::fromArray($params),
        );
    }

    private function handleSessionFsExists(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsExists(
            SessionFsExistsRequest::fromArray($params),
        );
    }

    private function handleSessionFsStat(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsStat(
            SessionFsStatRequest::fromArray($params),
        );
    }

    private function handleSessionFsMkdir(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsMkdir(
            SessionFsMkdirRequest::fromArray($params),
        );
    }

    private function handleSessionFsReaddir(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsReaddir(
            SessionFsReaddirRequest::fromArray($params),
        );
    }

    private function handleSessionFsReaddirWithTypes(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsReaddirWithTypes(
            SessionFsReaddirWithTypesRequest::fromArray($params),
        );
    }

    private function handleSessionFsRm(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsRm(
            SessionFsRmRequest::fromArray($params),
        );
    }

    private function handleSessionFsRename(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsRename(
            SessionFsRenameRequest::fromArray($params),
        );
    }

    private function handleSessionFsSqliteQuery(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsSqliteQuery(
            SessionFsSqliteQueryRequest::fromArray($params),
        );
    }

    private function handleSessionFsSqliteTransaction(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsSqliteTransaction(
            SessionFsSqliteTransactionRequest::fromArray($params),
        );
    }

    private function handleSessionFsSqliteExists(array $params): array
    {
        return $this->sessionForFsRequest($params)->handleSessionFsSqliteExists(
            SessionFsSqliteExistsRequest::fromArray($params),
        );
    }

    /**
     * Validate the `sessionFs` / `session_fs` client option.
     *
     * @throws InvalidArgumentException
     */
    private function normalizeSessionFsConfig(array $options): ?SessionFsConfig
    {
        $config = $options['sessionFs'] ?? $options['session_fs'] ?? null;

        if ($config === null) {
            return null;
        }

        if ($config instanceof Arrayable) {
            $config = $config->toArray();
        }

        if (! $config instanceof SessionFsConfig) {
            if (! is_array($config)) {
                throw new InvalidArgumentException('sessionFs must be a SessionFsConfig instance or an array.');
            }

            $config = SessionFsConfig::fromArray($config);
        }

        $config->validate();

        return $config;
    }

    /**
     * Send `sessionFs.setProvider` exactly once during start(). A failure stops the connection.
     *
     * @throws Throwable
     */
    private function registerSessionFsWithRuntime(): void
    {
        if ($this->sessionFsConfig === null) {
            return;
        }

        try {
            $this->rpc()->sessionFs()->setProvider($this->sessionFsConfig->toSetProviderRequest());
        } catch (Throwable $e) {
            try {
                $this->stop();
            } catch (Throwable) {
                // The original startup failure is the useful one.
            }

            throw $e;
        }
    }

    /**
     * When the client enables sessionFs, every session must supply `createSessionFsProvider`.
     *
     * @return (callable(CopilotSession): mixed)|null
     *
     * @throws InvalidArgumentException
     */
    private function resolveSessionFsFactory(array $config): ?callable
    {
        if ($this->sessionFsConfig === null) {
            return null;
        }

        $factory = $config['createSessionFsProvider'] ?? null;

        if (! is_callable($factory)) {
            throw new InvalidArgumentException(
                'createSessionFsProvider is required in session config when sessionFs is enabled in client options.',
            );
        }

        return $factory instanceof Closure ? $factory : Closure::fromCallable($factory);
    }

    /**
     * Invoke the factory for a session and verify the provider satisfies the declared capabilities.
     *
     * @param  callable(CopilotSession): mixed  $factory
     * @return SessionFsProvider|array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    private function createSessionFsProviderFor(Session $session, callable $factory): SessionFsProvider|array
    {
        $provider = $factory($session);

        if ($provider === null) {
            throw new InvalidArgumentException('createSessionFsProvider must return a SessionFsProvider.');
        }

        $this->validateSessionFsProvider($provider);

        if ($this->sessionFsConfig?->supportsSqlite() && ! SessionFsProviderSupport::supportsSqlite($provider)) {
            throw new InvalidArgumentException(
                'SessionFsConfig declares capabilities.sqlite but the provider does not implement sqlite.',
            );
        }

        if ($this->sessionFsConfig?->supportsBinary() && ! SessionFsProviderSupport::supportsBinary($provider)) {
            throw new InvalidArgumentException(
                'SessionFsConfig declares capabilities.binary but the provider does not implement readFileBytes and writeFileBytes.',
            );
        }

        return $provider;
    }

    private function validateSessionFsProvider(mixed $provider): void
    {
        if (
            $provider !== null
            && ! $provider instanceof SessionFsProvider
            && (! is_array($provider)
                || ! is_callable($provider['readFile'] ?? null)
                || ! is_callable($provider['writeFile'] ?? null))
        ) {
            throw new InvalidArgumentException(
                'sessionFsProvider must implement SessionFsProvider or provide callable readFile and writeFile entries.',
            );
        }
    }
}
