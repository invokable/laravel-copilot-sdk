<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ServerSkillList;
use Revolution\Copilot\Types\Rpc\SkillApplyInstallRequest;
use Revolution\Copilot\Types\Rpc\SkillApplyUninstallRequest;
use Revolution\Copilot\Types\Rpc\SkillDiscoveryPathList;
use Revolution\Copilot\Types\Rpc\SkillInstallationManagementResult;
use Revolution\Copilot\Types\Rpc\SkillPlanInstallFromReferenceRequest;
use Revolution\Copilot\Types\Rpc\SkillPlanInstallRequest;
use Revolution\Copilot\Types\Rpc\SkillPlanUninstallRequest;
use Revolution\Copilot\Types\Rpc\SkillsDiscoverRequest;
use Revolution\Copilot\Types\Rpc\SkillsGetDiscoveryPathsRequest;

/**
 * Pending server-level skills RPC operations.
 *
 * Usage:
 * ```php
 * $client->rpc()->skills()->discover();
 * $client->rpc()->skills()->config()->setDisabledSkills(['skill-name']);
 * ```
 */
class PendingServerSkills
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Skills config operations.
     */
    public function config(): PendingServerSkillsConfig
    {
        return new PendingServerSkillsConfig($this->client);
    }

    /**
     * Skill installation lifecycle operations.
     *
     * @experimental
     */
    public function installations(): PendingServerSkillsInstallations
    {
        return new PendingServerSkillsInstallations($this->client);
    }

    /**
     * Discover all available skills.
     */
    public function discover(SkillsDiscoverRequest|array $params = []): ServerSkillList
    {
        $paramsArray = ($params instanceof SkillsDiscoverRequest
            ? $params
            : SkillsDiscoverRequest::fromArray($params))->toArray();

        return ServerSkillList::fromArray(
            $this->client->request('skills.discover', $paramsArray),
        );
    }

    /**
     * Returns the canonical directories where a client may create skills that the runtime
     * will recognize, including ones that do not exist yet.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function getDiscoveryPaths(SkillsGetDiscoveryPathsRequest|array $params = []): SkillDiscoveryPathList
    {
        $paramsArray = ($params instanceof SkillsGetDiscoveryPathsRequest
            ? $params
            : SkillsGetDiscoveryPathsRequest::fromArray($params))->toArray();

        return SkillDiscoveryPathList::fromArray(
            $this->client->request('skills.getDiscoveryPaths', $paramsArray),
        );
    }

    /**
     * Creates a side-effect-free install plan for a candidate selected from the catalog.
     *
     * Pass the `candidateHandle` from a successful `catalog.select` result.
     *
     * @experimental
     */
    public function planInstall(SkillPlanInstallRequest|array $params): SkillInstallationManagementResult
    {
        $paramsArray = ($params instanceof SkillPlanInstallRequest
            ? $params
            : SkillPlanInstallRequest::fromArray($params))->toArray();

        return SkillInstallationManagementResult::fromArray(
            $this->client->request('skills.planInstall', $paramsArray),
        );
    }

    /**
     * Creates a side-effect-free install plan from a verified materialisation reference.
     *
     * The contract must require both `skill-confirmed-installation` and
     * `skill-reference-installation`.
     *
     * @experimental
     */
    public function planInstallFromReference(SkillPlanInstallFromReferenceRequest|array $params): SkillInstallationManagementResult
    {
        $paramsArray = ($params instanceof SkillPlanInstallFromReferenceRequest
            ? $params
            : SkillPlanInstallFromReferenceRequest::fromArray($params))->toArray();

        return SkillInstallationManagementResult::fromArray(
            $this->client->request('skills.planInstallFromReference', $paramsArray),
        );
    }

    /**
     * Applies a previously generated skill installation plan.
     *
     * @experimental
     */
    public function applyInstall(SkillApplyInstallRequest|array $params): SkillInstallationManagementResult
    {
        $paramsArray = ($params instanceof SkillApplyInstallRequest
            ? $params
            : SkillApplyInstallRequest::fromArray($params))->toArray();

        return SkillInstallationManagementResult::fromArray(
            $this->client->request('skills.applyInstall', $paramsArray),
        );
    }

    /**
     * Creates a side-effect-free removal plan for an owned skill installation.
     *
     * @experimental
     */
    public function planUninstall(SkillPlanUninstallRequest|array $params): SkillInstallationManagementResult
    {
        $paramsArray = ($params instanceof SkillPlanUninstallRequest
            ? $params
            : SkillPlanUninstallRequest::fromArray($params))->toArray();

        return SkillInstallationManagementResult::fromArray(
            $this->client->request('skills.planUninstall', $paramsArray),
        );
    }

    /**
     * Applies a previously generated skill removal plan.
     *
     * @experimental
     */
    public function applyUninstall(SkillApplyUninstallRequest|array $params): SkillInstallationManagementResult
    {
        $paramsArray = ($params instanceof SkillApplyUninstallRequest
            ? $params
            : SkillApplyUninstallRequest::fromArray($params))->toArray();

        return SkillInstallationManagementResult::fromArray(
            $this->client->request('skills.applyUninstall', $paramsArray),
        );
    }
}
