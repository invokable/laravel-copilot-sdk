<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Revolution\Copilot\Contracts\SessionFsBinaryProvider;
use Revolution\Copilot\Contracts\SessionFsProvider;
use Revolution\Copilot\Contracts\SessionFsSqliteProvider;
use Revolution\Copilot\Enums\SessionFSErrorCode;
use Revolution\Copilot\Enums\SessionFSSqliteQueryType;
use Revolution\Copilot\Enums\SessionFSSqliteTransactionErrorClass;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Exceptions\SessionFsSqliteTransactionFailure;
use Revolution\Copilot\Support\SessionFsProviderSupport;
use Revolution\Copilot\Types\Rpc\SessionFsAppendFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsEntry;
use Revolution\Copilot\Types\Rpc\SessionFsError;
use Revolution\Copilot\Types\Rpc\SessionFsExistsRequest;
use Revolution\Copilot\Types\Rpc\SessionFsExistsResult;
use Revolution\Copilot\Types\Rpc\SessionFsMkdirRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirResult;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirWithTypesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirWithTypesResult;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileBytesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileBytesResult;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileResult;
use Revolution\Copilot\Types\Rpc\SessionFsRenameRequest;
use Revolution\Copilot\Types\Rpc\SessionFsRmRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteExistsRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteExistsResult;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteQueryRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteQueryResult;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteTransactionError;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteTransactionRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteTransactionResult;
use Revolution\Copilot\Types\Rpc\SessionFsStatRequest;
use Revolution\Copilot\Types\Rpc\SessionFsStatResult;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileBytesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileRequest;
use Throwable;

trait HasSessionFs
{
    protected SessionFsProvider|array|null $sessionFsProvider = null;

    /**
     * Register the backing store used for session filesystem callbacks.
     *
     * @param  SessionFsProvider|array{readFile: callable, writeFile: callable, readFileBytes?: callable, writeFileBytes?: callable, appendFile?: callable, exists?: callable, stat?: callable, mkdir?: callable, readdir?: callable, readdirWithTypes?: callable, rm?: callable, rename?: callable, sqlite?: SessionFsSqliteProvider|array{query: callable, exists: callable, transaction?: callable}}|null  $provider
     *
     * @internal
     */
    public function registerSessionFsProvider(SessionFsProvider|array|null $provider): void
    {
        $this->sessionFsProvider = $provider;
    }

    public function handleSessionFsReadFile(SessionFsReadFileRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();

        try {
            $content = $provider instanceof SessionFsProvider
                ? $provider->readFile($request->path)
                : ($provider['readFile'])($request->path);

            return (new SessionFsReadFileResult($content))->toArray();
        } catch (Throwable $e) {
            return (new SessionFsReadFileResult('', $this->sessionFsError($e)))->toArray();
        }
    }

    public function handleSessionFsWriteFile(SessionFsWriteFileRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();

        try {
            if ($provider instanceof SessionFsProvider) {
                $provider->writeFile($request->path, $request->content, $request->mode);
            } else {
                ($provider['writeFile'])($request->path, $request->content, $request->mode);
            }

            return [];
        } catch (Throwable $e) {
            return $this->sessionFsError($e)->toArray();
        }
    }

    public function handleSessionFsReadFileBytes(SessionFsReadFileBytesRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();
        if (! $this->supportsSessionFsBinary($provider)) {
            return (new SessionFsReadFileBytesResult('', new SessionFsError(SessionFSErrorCode::UNKNOWN, 'Binary reads are not supported')))->toArray();
        }

        try {
            $bytes = $provider instanceof SessionFsBinaryProvider
                ? $provider->readFileBytes($request->path)
                : ($provider['readFileBytes'])($request->path);

            if (! is_string($bytes) || strlen($bytes) > 50_330_112) {
                throw new \UnexpectedValueException('sessionFs.readFileBytes content exceeds the binary read limit');
            }

            return (new SessionFsReadFileBytesResult(base64_encode($bytes)))->toArray();
        } catch (Throwable $e) {
            return (new SessionFsReadFileBytesResult('', $this->sessionFsError($e)))->toArray();
        }
    }

    public function handleSessionFsWriteFileBytes(SessionFsWriteFileBytesRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();
        if (! $this->supportsSessionFsBinary($provider)) {
            return (new SessionFsError(SessionFSErrorCode::UNKNOWN, 'Binary writes are not supported'))->toArray();
        }

        if (strlen($request->content) > 67_106_816) {
            return (new SessionFsError(SessionFSErrorCode::UNKNOWN, 'sessionFs.writeFileBytes content exceeds the binary write limit'))->toArray();
        }

        $bytes = base64_decode($request->content, true);
        if ($bytes === false || base64_encode($bytes) !== $request->content || strlen($bytes) > 50_330_112) {
            return (new SessionFsError(SessionFSErrorCode::UNKNOWN, 'invalid sessionFs.writeFileBytes base64 content'))->toArray();
        }

        try {
            if ($provider instanceof SessionFsBinaryProvider) {
                $provider->writeFileBytes($request->path, $bytes, $request->mode);
            } else {
                ($provider['writeFileBytes'])($request->path, $bytes, $request->mode);
            }

            return [];
        } catch (Throwable $e) {
            return $this->sessionFsError($e)->toArray();
        }
    }

    public function handleSessionFsAppendFile(SessionFsAppendFileRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();
        $method = SessionFsProviderSupport::filesystemMethod($provider, 'appendFile');
        if ($method === null) {
            return $this->unsupportedSessionFsOperation('appendFile');
        }

        try {
            $method($request->path, $request->content, $request->mode);

            return [];
        } catch (Throwable $e) {
            return $this->sessionFsError($e)->toArray();
        }
    }

    public function handleSessionFsExists(SessionFsExistsRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();
        $method = SessionFsProviderSupport::filesystemMethod($provider, 'exists');
        if ($method === null) {
            return (new SessionFsExistsResult(false))->toArray();
        }

        try {
            return (new SessionFsExistsResult((bool) $method($request->path)))->toArray();
        } catch (Throwable) {
            return (new SessionFsExistsResult(false))->toArray();
        }
    }

    public function handleSessionFsStat(SessionFsStatRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();
        $method = SessionFsProviderSupport::filesystemMethod($provider, 'stat');
        if ($method === null) {
            return $this->failedSessionFsStat(new SessionFsError(SessionFSErrorCode::UNKNOWN, 'sessionFs.stat is not supported by this provider'));
        }

        try {
            $result = $method($request->path);
            $result = $result instanceof SessionFsStatResult ? $result : SessionFsStatResult::fromArray((array) $result);

            // Providers signal errors by throwing, so never forward an error field.
            return (new SessionFsStatResult(
                birthtime: $result->birthtime,
                isDirectory: $result->isDirectory,
                isFile: $result->isFile,
                mtime: $result->mtime,
                size: $result->size,
            ))->toArray();
        } catch (Throwable $e) {
            return $this->failedSessionFsStat($this->sessionFsError($e));
        }
    }

    public function handleSessionFsMkdir(SessionFsMkdirRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();
        $method = SessionFsProviderSupport::filesystemMethod($provider, 'mkdir');
        if ($method === null) {
            return $this->unsupportedSessionFsOperation('mkdir');
        }

        try {
            $method($request->path, $request->recursive ?? false, $request->mode);

            return [];
        } catch (Throwable $e) {
            return $this->sessionFsError($e)->toArray();
        }
    }

    public function handleSessionFsReaddir(SessionFsReaddirRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();
        $method = SessionFsProviderSupport::filesystemMethod($provider, 'readdir');
        if ($method === null) {
            return (new SessionFsReaddirResult([], new SessionFsError(SessionFSErrorCode::UNKNOWN, 'sessionFs.readdir is not supported by this provider')))->toArray();
        }

        try {
            return (new SessionFsReaddirResult(array_values(array_map('strval', $method($request->path)))))->toArray();
        } catch (Throwable $e) {
            return (new SessionFsReaddirResult([], $this->sessionFsError($e)))->toArray();
        }
    }

    public function handleSessionFsReaddirWithTypes(SessionFsReaddirWithTypesRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();
        $method = SessionFsProviderSupport::filesystemMethod($provider, 'readdirWithTypes');
        if ($method === null) {
            return (new SessionFsReaddirWithTypesResult([], new SessionFsError(SessionFSErrorCode::UNKNOWN, 'sessionFs.readdirWithTypes is not supported by this provider')))->toArray();
        }

        try {
            $entries = array_values(array_map(
                fn (SessionFsEntry|array $entry) => $entry instanceof SessionFsEntry ? $entry : SessionFsEntry::fromArray($entry),
                $method($request->path),
            ));

            return (new SessionFsReaddirWithTypesResult($entries))->toArray();
        } catch (Throwable $e) {
            return (new SessionFsReaddirWithTypesResult([], $this->sessionFsError($e)))->toArray();
        }
    }

    public function handleSessionFsRm(SessionFsRmRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();
        $method = SessionFsProviderSupport::filesystemMethod($provider, 'rm');
        if ($method === null) {
            return $this->unsupportedSessionFsOperation('rm');
        }

        try {
            $method($request->path, $request->recursive ?? false, $request->force ?? false);

            return [];
        } catch (Throwable $e) {
            return $this->sessionFsError($e)->toArray();
        }
    }

    public function handleSessionFsRename(SessionFsRenameRequest $request): array
    {
        $provider = $this->requireSessionFsProvider();
        $method = SessionFsProviderSupport::filesystemMethod($provider, 'rename');
        if ($method === null) {
            return $this->unsupportedSessionFsOperation('rename');
        }

        try {
            $method($request->src, $request->dest);

            return [];
        } catch (Throwable $e) {
            return $this->sessionFsError($e)->toArray();
        }
    }

    /**
     * Unlike the filesystem methods, SQL failures propagate to the JSON-RPC layer so the
     * original message is preserved in the error response.
     */
    public function handleSessionFsSqliteQuery(SessionFsSqliteQueryRequest $request): array
    {
        $sqlite = SessionFsProviderSupport::sqlite($this->requireSessionFsProvider());
        if ($sqlite === null) {
            throw new \RuntimeException('SQLite is not supported by this provider');
        }

        $result = $this->runSessionFsSqliteQuery($sqlite, $request->queryType, $request->query, $request->params);

        return $result->toArray();
    }

    /**
     * Transaction failures carry a classification the runtime uses to decide whether a
     * retry is safe, so they are reported as a result-level error instead of a JSON-RPC error.
     */
    public function handleSessionFsSqliteTransaction(SessionFsSqliteTransactionRequest $request): array
    {
        $sqlite = SessionFsProviderSupport::sqlite($this->requireSessionFsProvider());
        $transaction = $sqlite !== null ? SessionFsProviderSupport::sqliteTransaction($sqlite) : null;
        if ($transaction === null) {
            return (new SessionFsSqliteTransactionResult([], new SessionFsSqliteTransactionError(
                SessionFSSqliteTransactionErrorClass::Fatal,
                'SQLite transactions are not supported by this provider',
            )))->toArray();
        }

        try {
            $results = array_values(array_map(
                fn (SessionFsSqliteQueryResult|array|null $result) => $this->normalizeSessionFsSqliteResult($result),
                $transaction($request->statements),
            ));

            return (new SessionFsSqliteTransactionResult($results))->toArray();
        } catch (Throwable $e) {
            return (new SessionFsSqliteTransactionResult([], new SessionFsSqliteTransactionError(
                $e instanceof SessionFsSqliteTransactionFailure
                    ? $e->errorClass
                    : SessionFSSqliteTransactionErrorClass::Fatal,
                $e->getMessage(),
            )))->toArray();
        }
    }

    public function handleSessionFsSqliteExists(SessionFsSqliteExistsRequest $request): array
    {
        $sqlite = SessionFsProviderSupport::sqlite($this->requireSessionFsProvider());
        if ($sqlite === null) {
            throw new \RuntimeException('SQLite is not supported by this provider');
        }

        $exists = $sqlite instanceof SessionFsSqliteProvider ? $sqlite->exists() : ($sqlite['exists'])();

        return (new SessionFsSqliteExistsResult((bool) $exists))->toArray();
    }

    /**
     * @param  SessionFsSqliteProvider|array{query: callable}  $sqlite
     * @param  array<string, mixed>|null  $params
     */
    private function runSessionFsSqliteQuery(SessionFsSqliteProvider|array $sqlite, SessionFSSqliteQueryType $queryType, string $query, ?array $params): SessionFsSqliteQueryResult
    {
        $result = $sqlite instanceof SessionFsSqliteProvider
            ? $sqlite->query($queryType, $query, $params)
            : ($sqlite['query'])($queryType, $query, $params);

        return $this->normalizeSessionFsSqliteResult($result);
    }

    private function normalizeSessionFsSqliteResult(SessionFsSqliteQueryResult|array|null $result): SessionFsSqliteQueryResult
    {
        if ($result === null) {
            return new SessionFsSqliteQueryResult(columns: [], rows: [], rowsAffected: 0);
        }

        $result = $result instanceof SessionFsSqliteQueryResult ? $result : SessionFsSqliteQueryResult::fromArray($result);

        // Providers signal errors by throwing, so never forward an error field.
        return new SessionFsSqliteQueryResult(
            columns: $result->columns,
            rows: $result->rows,
            rowsAffected: $result->rowsAffected,
            lastInsertRowid: $result->lastInsertRowid,
        );
    }

    private function failedSessionFsStat(SessionFsError $error): array
    {
        $now = gmdate('Y-m-d\TH:i:s.v\Z');

        return (new SessionFsStatResult(
            birthtime: $now,
            isDirectory: false,
            isFile: false,
            mtime: $now,
            size: 0,
            error: $error,
        ))->toArray();
    }

    private function unsupportedSessionFsOperation(string $operation): array
    {
        return (new SessionFsError(SessionFSErrorCode::UNKNOWN, "sessionFs.{$operation} is not supported by this provider"))->toArray();
    }

    private function requireSessionFsProvider(): SessionFsProvider|array
    {
        if ($this->sessionFsProvider === null) {
            throw new JsonRpcException(-32603, 'No SessionFs provider configured for session');
        }

        return $this->sessionFsProvider;
    }

    private function supportsSessionFsBinary(SessionFsProvider|array $provider): bool
    {
        return SessionFsProviderSupport::supportsBinary($provider);
    }

    private function sessionFsError(Throwable $exception): SessionFsError
    {
        $properties = get_object_vars($exception);
        $errorCode = $properties['code'] ?? $exception->getCode();
        $isNotFound = $errorCode === 'ENOENT' || $errorCode === 2;

        return new SessionFsError(
            code: $isNotFound ? SessionFSErrorCode::ENOENT : SessionFSErrorCode::UNKNOWN,
            message: $exception->getMessage(),
        );
    }
}
