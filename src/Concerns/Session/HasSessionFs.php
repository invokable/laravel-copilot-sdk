<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Revolution\Copilot\Contracts\SessionFsBinaryProvider;
use Revolution\Copilot\Contracts\SessionFsProvider;
use Revolution\Copilot\Enums\SessionFSErrorCode;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Types\Rpc\SessionFsError;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileBytesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileBytesResult;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileResult;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileBytesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileBytesResult;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileResult;
use Throwable;

trait HasSessionFs
{
    protected SessionFsProvider|array|null $sessionFsProvider = null;

    /**
     * Register the backing store used for session filesystem callbacks.
     *
     * @param  SessionFsProvider|array{readFile: callable, writeFile: callable, readFileBytes?: callable, writeFileBytes?: callable}|null  $provider
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
            return (new SessionFsWriteFileResult($this->sessionFsError($e)))->toArray();
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
            return (new SessionFsWriteFileBytesResult(new SessionFsError(SessionFSErrorCode::UNKNOWN, 'Binary writes are not supported')))->toArray();
        }

        if (strlen($request->content) > 67_106_816) {
            return (new SessionFsWriteFileBytesResult(new SessionFsError(SessionFSErrorCode::UNKNOWN, 'sessionFs.writeFileBytes content exceeds the binary write limit')))->toArray();
        }

        $bytes = base64_decode($request->content, true);
        if ($bytes === false || base64_encode($bytes) !== $request->content || strlen($bytes) > 50_330_112) {
            return (new SessionFsWriteFileBytesResult(new SessionFsError(SessionFSErrorCode::UNKNOWN, 'invalid sessionFs.writeFileBytes base64 content')))->toArray();
        }

        try {
            if ($provider instanceof SessionFsBinaryProvider) {
                $provider->writeFileBytes($request->path, $bytes, $request->mode);
            } else {
                ($provider['writeFileBytes'])($request->path, $bytes, $request->mode);
            }

            return [];
        } catch (Throwable $e) {
            return (new SessionFsWriteFileBytesResult($this->sessionFsError($e)))->toArray();
        }
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
        return $provider instanceof SessionFsBinaryProvider
            || (is_callable($provider['readFileBytes'] ?? null) && is_callable($provider['writeFileBytes'] ?? null));
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
