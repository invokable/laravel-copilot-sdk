<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionSkillsDisableParams;
use Revolution\Copilot\Types\Rpc\SessionSkillsEnableParams;
use Revolution\Copilot\Types\Rpc\SessionSkillsListResult;

/**
 * Pending skills RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingSkills
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * List available skills.
     */
    public function list(): SessionSkillsListResult
    {
        return SessionSkillsListResult::fromArray(
            $this->client->request('session.skills.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Enable a skill.
     */
    public function enable(SessionSkillsEnableParams|array $params): array
    {
        $paramsArray = ($params instanceof SessionSkillsEnableParams ? $params : SessionSkillsEnableParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.skills.enable', $paramsArray);
    }

    /**
     * Disable a skill.
     */
    public function disable(SessionSkillsDisableParams|array $params): array
    {
        $paramsArray = ($params instanceof SessionSkillsDisableParams ? $params : SessionSkillsDisableParams::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.skills.disable', $paramsArray);
    }

    /**
     * Reload skills.
     */
    public function reload(): array
    {
        return $this->client->request('session.skills.reload', [
            'sessionId' => $this->sessionId,
        ]);
    }
}
