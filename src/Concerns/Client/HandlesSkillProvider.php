<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Client;

use Revolution\Copilot\Contracts\SkillProvider;
use Revolution\Copilot\Session;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\Rpc\SkillProviderListRequest;
use Revolution\Copilot\Types\Rpc\SkillProviderReadRequest;
use RuntimeException;

trait HandlesSkillProvider
{
    private function handleSkillProviderList(array $params, CancellationToken $token): array
    {
        $request = SkillProviderListRequest::fromArray($params);
        $session = $this->sessions[$request->sessionId] ?? null;

        if (! $session instanceof Session) {
            throw new RuntimeException("Session not found: {$request->sessionId}");
        }

        return $session->handleSkillProviderList($request, $token)->toArray();
    }

    private function handleSkillProviderRead(array $params, CancellationToken $token): array
    {
        $request = SkillProviderReadRequest::fromArray($params);
        $session = $this->sessions[$request->sessionId] ?? null;

        if (! $session instanceof Session) {
            throw new RuntimeException("Session not found: {$request->sessionId}");
        }

        return $session->handleSkillProviderRead($request, $token)->toArray();
    }

    private function validateSkillProvider(mixed $provider): void
    {
        if (
            $provider !== null
            && ! $provider instanceof SkillProvider
            && (! is_array($provider)
                || ! is_callable($provider['listSkills'] ?? null)
                || ! is_callable($provider['readSkill'] ?? null))
        ) {
            throw new \InvalidArgumentException(
                'skillProvider must implement SkillProvider or provide callable listSkills and readSkill entries.',
            );
        }
    }
}
