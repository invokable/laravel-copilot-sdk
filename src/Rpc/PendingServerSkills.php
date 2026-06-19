<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ServerSkillList;
use Revolution\Copilot\Types\Rpc\SkillDiscoveryPathList;
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
}
