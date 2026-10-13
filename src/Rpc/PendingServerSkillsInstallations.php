<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
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
     * @return array<string, mixed>
     *
     * @experimental
     */
    public function list(SkillInstallationsRequest|array $params): array
    {
        $paramsArray = ($params instanceof SkillInstallationsRequest
            ? $params
            : SkillInstallationsRequest::fromArray($params))->toArray();

        return $this->client->request('skills.installations.list', $paramsArray);
    }

    /**
     * Recovers pending skill installation operations.
     *
     * @return array<string, mixed>
     *
     * @experimental
     */
    public function recover(SkillInstallationsRequest|array $params): array
    {
        $paramsArray = ($params instanceof SkillInstallationsRequest
            ? $params
            : SkillInstallationsRequest::fromArray($params))->toArray();

        return $this->client->request('skills.installations.recover', $paramsArray);
    }

    /**
     * Gets the status of a known skill installation operation.
     *
     * @return array<string, mixed>
     *
     * @experimental
     */
    public function status(SkillInstallationOperationRequest|array $params): array
    {
        $paramsArray = ($params instanceof SkillInstallationOperationRequest
            ? $params
            : SkillInstallationOperationRequest::fromArray($params))->toArray();

        return $this->client->request('skills.installations.status', $paramsArray);
    }

    /**
     * Cancels a known skill installation operation.
     *
     * @return array<string, mixed>
     *
     * @experimental
     */
    public function cancel(SkillInstallationOperationRequest|array $params): array
    {
        $paramsArray = ($params instanceof SkillInstallationOperationRequest
            ? $params
            : SkillInstallationOperationRequest::fromArray($params))->toArray();

        return $this->client->request('skills.installations.cancel', $paramsArray);
    }

    /**
     * Changes whether an owned skill installation is enabled.
     *
     * @return array<string, mixed>
     *
     * @experimental
     */
    public function setEnabled(SkillSetEnabledRequest|array $params): array
    {
        $paramsArray = ($params instanceof SkillSetEnabledRequest
            ? $params
            : SkillSetEnabledRequest::fromArray($params))->toArray();

        return $this->client->request('skills.installations.setEnabled', $paramsArray);
    }
}
