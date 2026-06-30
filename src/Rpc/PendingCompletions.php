<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\CompletionsGetTriggerCharactersResult;
use Revolution\Copilot\Types\Rpc\CompletionsRequestRequest;
use Revolution\Copilot\Types\Rpc\CompletionsRequestResult;

/**
 * Pending completions RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingCompletions
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Gets the characters that should trigger host-driven completions for the session.
     * Empty disables host-driven completions (e.g. local sessions, or a relay host that does not advertise them).
     */
    public function getTriggerCharacters(): CompletionsGetTriggerCharactersResult
    {
        return CompletionsGetTriggerCharactersResult::fromArray(
            $this->client->request('session.completions.getTriggerCharacters', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Requests host-driven completion items for the current composer input.
     * Returns an empty list when the host has no items or does not support completions.
     */
    public function request(CompletionsRequestRequest|array $params): CompletionsRequestResult
    {
        $paramsArray = ($params instanceof CompletionsRequestRequest ? $params : CompletionsRequestRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return CompletionsRequestResult::fromArray(
            $this->client->request('session.completions.request', $paramsArray),
        );
    }
}
