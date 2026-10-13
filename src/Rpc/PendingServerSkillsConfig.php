<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SkillsConfigSetDisabledSkillsRequest;
use Revolution\Copilot\Types\Rpc\SkillsConfigSetSkillDisabledRequest;

/**
 * Pending server-level skills config RPC operations.
 */
class PendingServerSkillsConfig
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Set globally disabled skills.
     */
    public function setDisabledSkills(SkillsConfigSetDisabledSkillsRequest|array $params): void
    {
        $paramsArray = ($params instanceof SkillsConfigSetDisabledSkillsRequest
            ? $params
            : SkillsConfigSetDisabledSkillsRequest::fromArray($params))->toArray();

        $this->client->request('skills.config.setDisabledSkills', $paramsArray);
    }

    /**
     * Set whether a skill is disabled in the global skills configuration.
     *
     * @experimental
     */
    public function setSkillDisabled(SkillsConfigSetSkillDisabledRequest|array $params): void
    {
        $paramsArray = ($params instanceof SkillsConfigSetSkillDisabledRequest
            ? $params
            : SkillsConfigSetSkillDisabledRequest::fromArray($params))->toArray();

        $this->client->request('skills.config.setSkillDisabled', $paramsArray);
    }
}
