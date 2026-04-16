<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SkillsConfigSetDisabledSkillsRequest;

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
}
