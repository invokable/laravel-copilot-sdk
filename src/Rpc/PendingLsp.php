<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\LspInitializeRequest;
use Revolution\Copilot\Types\Rpc\SessionLspDiagnoseRequest;
use Revolution\Copilot\Types\Rpc\SessionLspDiagnoseResult;
use Revolution\Copilot\Types\Rpc\SessionLspListResult;
use Revolution\Copilot\Types\Rpc\SessionLspSetRequest;
use Revolution\Copilot\Types\Rpc\SessionLspStartResult;

/**
 * Language-server protocol operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingLsp
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function initialize(LspInitializeRequest|array $params = []): void
    {
        $paramsArray = $params instanceof LspInitializeRequest
            ? $params->toArray()
            : LspInitializeRequest::fromArray($params)->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.lsp.initialize', $paramsArray);
    }

    public function list(LspInitializeRequest|array $params = []): SessionLspListResult
    {
        $paramsArray = $params instanceof LspInitializeRequest
            ? $params->toArray()
            : LspInitializeRequest::fromArray($params)->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionLspListResult::fromArray(
            $this->client->request('session.lsp.list', $paramsArray),
        );
    }

    public function start(LspInitializeRequest|array $params = []): SessionLspStartResult
    {
        $paramsArray = $params instanceof LspInitializeRequest
            ? $params->toArray()
            : LspInitializeRequest::fromArray($params)->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionLspStartResult::fromArray(
            $this->client->request('session.lsp.start', $paramsArray),
        );
    }

    public function stop(): void
    {
        $this->client->request('session.lsp.stop', [
            'sessionId' => $this->sessionId,
        ]);
    }

    public function diagnose(SessionLspDiagnoseRequest|array $params): SessionLspDiagnoseResult
    {
        $paramsArray = $params instanceof SessionLspDiagnoseRequest
            ? $params->toArray()
            : SessionLspDiagnoseRequest::fromArray($params)->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionLspDiagnoseResult::fromArray(
            $this->client->request('session.lsp.diagnose', $paramsArray),
        );
    }

    public function set(SessionLspSetRequest|array $params): void
    {
        $paramsArray = $params instanceof SessionLspSetRequest
            ? $params->toArray()
            : SessionLspSetRequest::fromArray($params)->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.lsp.set', $paramsArray);
    }
}
