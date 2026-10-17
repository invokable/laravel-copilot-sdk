<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SandboxCredentialSuggestionsResult;
use Revolution\Copilot\Types\Rpc\SandboxDisableForSessionRequest;
use Revolution\Copilot\Types\Rpc\SandboxDisableForSessionResult;
use Revolution\Copilot\Types\Rpc\SandboxEnforcementStatus;
use Revolution\Copilot\Types\Rpc\SandboxGrantPathForRequestRequest;
use Revolution\Copilot\Types\Rpc\SandboxGrantPathForRequestResult;

/**
 * Pending sandbox RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingSandbox
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Return managed sandbox enforcement state for this session.
     */
    public function getEnforcementStatus(): SandboxEnforcementStatus
    {
        return SandboxEnforcementStatus::fromArray(
            $this->client->request('session.sandbox.getEnforcementStatus', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * List possible unconfigured credentials for new sandboxed shells without exposing their values.
     *
     * @experimental This API is experimental and may change or be removed.
     */
    public function getCredentialSuggestions(): SandboxCredentialSuggestionsResult
    {
        return SandboxCredentialSuggestionsResult::fromArray(
            $this->client->request('session.sandbox.getCredentialSuggestions', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Disable sandboxing for the remainder of the current session and approve the
     * referenced pending sandbox-bypass permission request.
     */
    public function disableForSession(SandboxDisableForSessionRequest|array $params): SandboxDisableForSessionResult
    {
        $paramsArray = ($params instanceof SandboxDisableForSessionRequest ? $params : SandboxDisableForSessionRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SandboxDisableForSessionResult::fromArray(
            $this->client->request('session.sandbox.disableForSession', $paramsArray),
        );
    }

    /**
     * Accept the sandbox path grant offered on an active sandbox escalation permission
     * request, adding the path to the session's sandbox policy and approving the request
     * so the blocked operation re-runs inside the sandbox instead of outside it.
     *
     * Prefer this minimum-privilege grant over disableForSession() when only a specific
     * path needs to be allowed. This does not persist the path; the grant applies only to
     * the current session and is not saved for future sessions.
     *
     * @experimental This API is experimental and may change or be removed.
     */
    public function grantPathForRequest(SandboxGrantPathForRequestRequest|array $params): SandboxGrantPathForRequestResult
    {
        $paramsArray = ($params instanceof SandboxGrantPathForRequestRequest ? $params : SandboxGrantPathForRequestRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SandboxGrantPathForRequestResult::fromArray(
            $this->client->request('session.sandbox.grantPathForRequest', $paramsArray),
        );
    }
}
