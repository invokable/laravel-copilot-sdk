<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Client;

use Revolution\Copilot\Contracts\SessionFsProvider;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileBytesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileBytesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileRequest;
use RuntimeException;

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

    private function validateSessionFsProvider(mixed $provider): void
    {
        if (
            $provider !== null
            && ! $provider instanceof SessionFsProvider
            && (! is_array($provider)
                || ! is_callable($provider['readFile'] ?? null)
                || ! is_callable($provider['writeFile'] ?? null))
        ) {
            throw new \InvalidArgumentException(
                'sessionFsProvider must implement SessionFsProvider or provide callable readFile and writeFile entries.',
            );
        }
    }
}
