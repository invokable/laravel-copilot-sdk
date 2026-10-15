<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\PluginApplyInstallRequest;
use Revolution\Copilot\Types\Rpc\PluginInstallationManagementResult;
use Revolution\Copilot\Types\Rpc\PluginInstallationResult;
use Revolution\Copilot\Types\Rpc\PluginInstallResult;
use Revolution\Copilot\Types\Rpc\PluginList;
use Revolution\Copilot\Types\Rpc\PluginPlanInstallRequest;
use Revolution\Copilot\Types\Rpc\PluginsDisableRequest;
use Revolution\Copilot\Types\Rpc\PluginsEnableRequest;
use Revolution\Copilot\Types\Rpc\PluginsInstallRequest;
use Revolution\Copilot\Types\Rpc\PluginsUninstallRequest;
use Revolution\Copilot\Types\Rpc\PluginsUpdateRequest;
use Revolution\Copilot\Types\Rpc\PluginUpdateAllResult;
use Revolution\Copilot\Types\Rpc\PluginUpdateResult;

/**
 * Pending server-level plugin RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerPlugins
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Creates a side-effect-free install plan for a selected Agent Plugin candidate.
     *
     * @experimental
     */
    public function planInstall(PluginPlanInstallRequest|array $params): PluginInstallationManagementResult
    {
        $paramsArray = ($params instanceof PluginPlanInstallRequest
            ? $params
            : PluginPlanInstallRequest::fromArray($params))->toArray();

        return PluginInstallationManagementResult::fromArray(
            $this->client->request('plugins.planInstall', $paramsArray),
        );
    }

    /**
     * Applies a previously generated Agent Plugin plan after requesting explicit confirmation.
     *
     * @experimental
     */
    public function applyInstall(PluginApplyInstallRequest|array $params): PluginInstallationResult
    {
        $paramsArray = ($params instanceof PluginApplyInstallRequest
            ? $params
            : PluginApplyInstallRequest::fromArray($params))->toArray();

        return PluginInstallationResult::fromArray(
            $this->client->request('plugins.applyInstall', $paramsArray),
        );
    }

    /**
     * List plugins installed in user/global state.
     */
    public function list(): PluginList
    {
        return PluginList::fromArray(
            $this->client->request('plugins.list', []),
        );
    }

    /**
     * Install a plugin.
     */
    public function install(PluginsInstallRequest|array $params): PluginInstallResult
    {
        $paramsArray = ($params instanceof PluginsInstallRequest
            ? $params
            : PluginsInstallRequest::fromArray($params))->toArray();

        return PluginInstallResult::fromArray(
            $this->client->request('plugins.install', $paramsArray),
        );
    }

    /**
     * Uninstall an installed plugin.
     */
    public function uninstall(PluginsUninstallRequest|array $params): void
    {
        $paramsArray = ($params instanceof PluginsUninstallRequest
            ? $params
            : PluginsUninstallRequest::fromArray($params))->toArray();

        $this->client->request('plugins.uninstall', $paramsArray);
    }

    /**
     * Update an installed plugin.
     */
    public function update(PluginsUpdateRequest|array $params): PluginUpdateResult
    {
        $paramsArray = ($params instanceof PluginsUpdateRequest
            ? $params
            : PluginsUpdateRequest::fromArray($params))->toArray();

        return PluginUpdateResult::fromArray(
            $this->client->request('plugins.update', $paramsArray),
        );
    }

    /**
     * Update every installed plugin.
     */
    public function updateAll(): PluginUpdateAllResult
    {
        return PluginUpdateAllResult::fromArray(
            $this->client->request('plugins.updateAll', []),
        );
    }

    /**
     * Enable installed plugins for new sessions.
     */
    public function enable(PluginsEnableRequest|array $params): void
    {
        $paramsArray = ($params instanceof PluginsEnableRequest
            ? $params
            : PluginsEnableRequest::fromArray($params))->toArray();

        $this->client->request('plugins.enable', $paramsArray);
    }

    /**
     * Disable installed plugins for new sessions.
     */
    public function disable(PluginsDisableRequest|array $params): void
    {
        $paramsArray = ($params instanceof PluginsDisableRequest
            ? $params
            : PluginsDisableRequest::fromArray($params))->toArray();

        $this->client->request('plugins.disable', $paramsArray);
    }

    /**
     * Built-in plugin directory operations.
     */
    public function builtin(): PendingServerPluginsBuiltin
    {
        return new PendingServerPluginsBuiltin($this->client);
    }

    /**
     * Plugin marketplace operations.
     */
    public function marketplaces(): PendingServerPluginsMarketplaces
    {
        return new PendingServerPluginsMarketplaces($this->client);
    }
}
