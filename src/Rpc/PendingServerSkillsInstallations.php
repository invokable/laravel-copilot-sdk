<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SkillInstallationManagementResult;
use Revolution\Copilot\Types\Rpc\SkillInstallationOperationRequest;
use Revolution\Copilot\Types\Rpc\SkillInstallationsRequest;
use Revolution\Copilot\Types\Rpc\SkillSetEnabledRequest;

/**
 * Skill installation operation management RPC methods.
 *
 * @experimental
 */
class PendingServerSkillsInstallations
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Lists owned skill installations.
     *
     * @experimental
     */
    public function list(SkillInstallationsRequest|array $params): SkillInstallationManagementResult
    {
        $paramsArray = ($params instanceof SkillInstallationsRequest
            ? $params
            : SkillInstallationsRequest::fromArray($params))->toArray();

        return SkillInstallationManagementResult::fromArray(
            $this->client->request('skills.installations.list', $paramsArray),
        );
    }

    /**
     * Recovers pending skill installation operations.
     *
     * @experimental
     */
    public function recover(SkillInstallationsRequest|array $params): SkillInstallationManagementResult
    {
        $paramsArray = ($params instanceof SkillInstallationsRequest
            ? $params
            : SkillInstallationsRequest::fromArray($params))->toArray();

        return SkillInstallationManagementResult::fromArray(
            $this->client->request('skills.installations.recover', $paramsArray),
        );
    }

    /**
     * Gets the status of a known skill installation operation.
     *
     * @experimental
     */
    public function status(SkillInstallationOperationRequest|array $params): SkillInstallationManagementResult
    {
        $paramsArray = ($params instanceof SkillInstallationOperationRequest
            ? $params
            : SkillInstallationOperationRequest::fromArray($params))->toArray();

        return SkillInstallationManagementResult::fromArray(
            $this->client->request('skills.installations.status', $paramsArray),
        );
    }

    /**
     * Cancels a known skill installation operation.
     *
     * @experimental
     */
    public function cancel(SkillInstallationOperationRequest|array $params): SkillInstallationManagementResult
    {
        $paramsArray = ($params instanceof SkillInstallationOperationRequest
            ? $params
            : SkillInstallationOperationRequest::fromArray($params))->toArray();

        return SkillInstallationManagementResult::fromArray(
            $this->client->request('skills.installations.cancel', $paramsArray),
        );
    }

    /**
     * Changes whether an owned skill installation is enabled.
     *
     * @experimental
     */
    public function setEnabled(SkillSetEnabledRequest|array $params): SkillInstallationManagementResult
    {
        $paramsArray = ($params instanceof SkillSetEnabledRequest
            ? $params
            : SkillSetEnabledRequest::fromArray($params))->toArray();

        return SkillInstallationManagementResult::fromArray(
            $this->client->request('skills.installations.setEnabled', $paramsArray),
        );
    }
}
