<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ServerSkillList;
use Revolution\Copilot\Types\Rpc\SkillsDiscoverRequest;

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
}
