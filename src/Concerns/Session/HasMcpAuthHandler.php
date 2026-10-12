<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpOauthPendingRequestResponseKind;
use Revolution\Copilot\Types\McpAuthRequest;
use Revolution\Copilot\Types\McpAuthResult;
use Revolution\Copilot\Types\McpAuthToken;
use Revolution\Copilot\Types\Rpc\McpOauthHandlePendingRequest;
use Revolution\Copilot\Types\Rpc\McpOauthPendingRequestResponse;
use Throwable;

/**
 * Manages host-provided MCP OAuth token handler registration and execution.
 *
 * @internal
 */
trait HasMcpAuthHandler
{
    /**
     * @var Closure(McpAuthRequest, array{sessionId: string}): McpAuthToken|McpAuthResult|array{accessToken?: string, tokenType?: string, expiresIn?: int, kind?: 'token'|'cancelled'}|null
     */
    protected ?Closure $mcpAuthHandler = null;

    /**
     * @param  Closure(McpAuthRequest, array{sessionId: string}): McpAuthToken|McpAuthResult|array{accessToken?: string, tokenType?: string, expiresIn?: int, kind?: 'token'|'cancelled'}|null  $handler
     *
     * @internal
     */
    public function registerMcpAuthHandler(?Closure $handler): void
    {
        $this->mcpAuthHandler = $handler;
    }

    /**
     * Execute an MCP OAuth handler and respond to the pending request.
     *
     * @internal
     */
    protected function executeMcpAuthAndRespond(McpAuthRequest $request): void
    {
        try {
            $result = ($this->mcpAuthHandler)($request, ['sessionId' => $this->sessionId]);
            $resultArray = match (true) {
                $result instanceof Arrayable => $result->toArray(),
                is_array($result) => $result,
                default => [],
            };

            $response = array_key_exists('accessToken', $resultArray)
                ? new McpOauthPendingRequestResponse(
                    kind: McpOauthPendingRequestResponseKind::Token,
                    accessToken: $resultArray['accessToken'],
                    expiresIn: $resultArray['expiresIn'] ?? null,
                    tokenType: $resultArray['tokenType'] ?? null,
                )
                : new McpOauthPendingRequestResponse(kind: McpOauthPendingRequestResponseKind::Cancelled);

            $this->rpc()->mcp()->handlePendingRequest(
                new McpOauthHandlePendingRequest(
                    requestId: $request->requestId,
                    result: $response,
                ),
            );
        } catch (Throwable) {
            try {
                $this->rpc()->mcp()->handlePendingRequest(
                    new McpOauthHandlePendingRequest(
                        requestId: $request->requestId,
                        result: new McpOauthPendingRequestResponse(
                            kind: McpOauthPendingRequestResponseKind::Cancelled,
                        ),
                    ),
                );
            } catch (Throwable) {
                // Connection lost or RPC error — nothing we can do
            }
        }
    }
}
