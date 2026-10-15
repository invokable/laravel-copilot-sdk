<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SkillList;
use Revolution\Copilot\Types\Rpc\SkillsDisableRequest;
use Revolution\Copilot\Types\Rpc\SkillsEnableRequest;
use Revolution\Copilot\Types\Rpc\SkillsGetInvokedResult;

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
    public function list(): SkillList
    {
        return SkillList::fromArray(
            $this->client->request('session.skills.list', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Enable a skill.
     */
    public function enable(SkillsEnableRequest|array $params): array
    {
        $paramsArray = ($params instanceof SkillsEnableRequest ? $params : SkillsEnableRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return $this->client->request('session.skills.enable', $paramsArray);
    }

    /**
     * Disable a skill.
     */
    public function disable(SkillsDisableRequest|array $params): array
    {
        $paramsArray = ($params instanceof SkillsDisableRequest ? $params : SkillsDisableRequest::fromArray($params))->toArray();
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

    /**
     * Get the skills invoked during this session.
     */
    public function getInvoked(): SkillsGetInvokedResult
    {
        return SkillsGetInvokedResult::fromArray(
            $this->client->request('session.skills.getInvoked', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Ensure skills are loaded without forcing a reload.
     */
    public function ensureLoaded(): void
    {
        $this->client->request('session.skills.ensureLoaded', [
            'sessionId' => $this->sessionId,
        ]);
    }
}
