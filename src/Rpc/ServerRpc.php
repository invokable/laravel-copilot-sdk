<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ConnectRequest;
use Revolution\Copilot\Types\Rpc\ConnectResult;
use Revolution\Copilot\Types\Rpc\PingRequest;
use Revolution\Copilot\Types\Rpc\PingResult;

/**
 * Typed server-scoped RPC methods (no session required).
 */
class ServerRpc
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Send a ping request.
     */
    public function ping(PingRequest|array $params = []): PingResult
    {
        $paramsArray = $params instanceof PingRequest ? $params->toArray() : $params;

        return PingResult::fromArray(
            $this->client->request('ping', $paramsArray),
        );
    }

    /**
     * Send a connect handshake request.
     *
     * @internal Part of the SDK's internal surface.
     */
    public function connect(ConnectRequest|array $params = []): ConnectResult
    {
        $paramsArray = $params instanceof ConnectRequest ? $params->toArray() : $params;

        return ConnectResult::fromArray(
            $this->client->request('connect', $paramsArray),
        );
    }

    /**
     * Register this client as the extension launch provider.
     */
    public function registerExtensionLaunchProvider(): void
    {
        $this->client->request('registerExtensionLaunchProvider', []);
    }

    /**
     * Models RPC operations.
     */
    public function models(): PendingServerModels
    {
        return new PendingServerModels($this->client);
    }

    /**
     * Experimental Azure ML local-model lifecycle RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function azureMl(): PendingServerAzureMl
    {
        return new PendingServerAzureMl($this->client);
    }

    /**
     * Server-scoped slash command RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function commands(): PendingServerCommands
    {
        return new PendingServerCommands($this->client);
    }

    /**
     * Server-scoped hooks RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function hooks(): PendingServerHooks
    {
        return new PendingServerHooks($this->client);
    }

    /**
     * Tools RPC operations.
     */
    public function tools(): PendingServerTools
    {
        return new PendingServerTools($this->client);
    }

    /**
     * Account RPC operations.
     */
    public function account(): PendingServerAccount
    {
        return new PendingServerAccount($this->client);
    }

    /**
     * Experimental GitHub Mission Control environment operations.
     */
    public function environments(): PendingServerEnvironments
    {
        return new PendingServerEnvironments($this->client);
    }

    /**
     * Experimental account broker operations.
     */
    public function accounts(): PendingServerAccounts
    {
        return new PendingServerAccounts($this->client);
    }

    /**
     * MCP configuration RPC operations.
     */
    public function mcp(): PendingServerMcpConfig
    {
        return new PendingServerMcpConfig($this->client);
    }

    /**
     * Host-generated secret redaction RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function secrets(): PendingServerSecrets
    {
        return new PendingServerSecrets($this->client);
    }

    /**
     * SessionFs RPC operations.
     */
    public function sessionFs(): PendingServerSessionFs
    {
        return new PendingServerSessionFs($this->client);
    }

    /**
     * Session lifecycle and management RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function sessions(): PendingServerSessions
    {
        return new PendingServerSessions($this->client);
    }

    /**
     * Server-level skills RPC operations.
     */
    public function skills(): PendingServerSkills
    {
        return new PendingServerSkills($this->client);
    }

    /**
     * Server-level plugin management RPC operations.
     */
    public function plugins(): PendingServerPlugins
    {
        return new PendingServerPlugins($this->client);
    }

    /**
     * Server-level agents RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function agents(): PendingServerAgents
    {
        return new PendingServerAgents($this->client);
    }

    /**
     * Connector catalog discovery operations.
     */
    public function connectors(): PendingServerConnectors
    {
        return new PendingServerConnectors($this->client);
    }

    /**
     * Sandbox credential-proxy certificate authority operations.
     *
     * @experimental
     */
    public function sandboxProxyCa(): PendingServerSandboxProxyCa
    {
        return new PendingServerSandboxProxyCa($this->client);
    }

    /**
     * Server-scoped sandbox RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function sandbox(): PendingServerSandbox
    {
        return new PendingServerSandbox($this->client);
    }

    /**
     * Git working-directory and remote repository discovery operations.
     */
    public function git(): PendingServerGit
    {
        return new PendingServerGit($this->client);
    }

    /**
     * GitHub repository identity lookups.
     */
    public function gitHubRepository(): PendingServerGitHubRepository
    {
        return new PendingServerGitHubRepository($this->client);
    }

    /**
     * Cancellable GitHub organization-owner discovery operations.
     */
    public function gitHubOwners(): PendingServerGitHubOwners
    {
        return new PendingServerGitHubOwners($this->client);
    }

    /**
     * Server-level extension RPC operations.
     *
     * @experimental This API group is part of an experimental API and may change or be removed.
     */
    public function extensions(): PendingServerExtensions
    {
        return new PendingServerExtensions($this->client);
    }

    /**
     * Server-level instructions RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function instructions(): PendingServerInstructions
    {
        return new PendingServerInstructions($this->client);
    }

    /**
     * Server-level user settings RPC operations.
     */
    public function userSettings(): PendingServerUserSettings
    {
        return new PendingServerUserSettings($this->client);
    }

    /**
     * Server-level managed settings RPC operations.
     *
     * @experimental This API group is experimental and may be changed or removed.
     */
    public function managedSettings(): PendingServerManagedSettings
    {
        return new PendingServerManagedSettings($this->client);
    }

    /**
     * Server-level settings file RPC operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function settings(): PendingServerSettings
    {
        return new PendingServerSettings($this->client);
    }

    /**
     * Content-exclusion policy RPC operations.
     */
    public function contentExclusion(): PendingServerContentExclusion
    {
        return new PendingServerContentExclusion($this->client);
    }

    /**
     * LLM inference callback provider RPC operations.
     *
     * Allows this client to register as the LLM inference provider and deliver
     * HTTP response frames for in-flight model-layer requests.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function llmInference(): PendingServerLlmInference
    {
        return new PendingServerLlmInference($this->client);
    }

    /**
     * Returns a pending catalog RPC handler.
     *
     * @experimental
     */
    public function catalog(): PendingServerCatalog
    {
        return new PendingServerCatalog($this->client);
    }

    /**
     * Host session catalog RPC operations.
     *
     * @experimental
     */
    public function host(): PendingServerHost
    {
        return new PendingServerHost($this->client);
    }

    /**
     * Runtime lifecycle RPC operations.
     *
     * @experimental
     */
    public function runtime(): PendingServerRuntime
    {
        return new PendingServerRuntime($this->client);
    }

    /**
     * Local agent registry RPC operations.
     *
     * @experimental
     */
    public function agentRegistry(): PendingServerAgentRegistry
    {
        return new PendingServerAgentRegistry($this->client);
    }
}
