<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Contracts\SkillProvider;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\Rpc\SkillProviderListRequest;
use Revolution\Copilot\Types\Rpc\SkillProviderListResult;
use Revolution\Copilot\Types\Rpc\SkillProviderReadRequest;
use Revolution\Copilot\Types\Rpc\SkillProviderReadResult;
use Revolution\Copilot\Types\SkillProviderCallOptions;
use Throwable;

trait HasSkillProvider
{
    protected SkillProvider|array|null $skillProvider = null;

    /**
     * Register the provider used for session-scoped skill callbacks.
     *
     * @param  SkillProvider|array{listSkills: callable, readSkill: callable}|null  $provider
     *
     * @internal
     */
    public function registerSkillProvider(SkillProvider|array|null $provider): void
    {
        $this->skillProvider = $provider;
    }

    /**
     * Handle the runtime's session-scoped skill catalog request.
     */
    public function handleSkillProviderList(
        SkillProviderListRequest $request,
        CancellationToken $token,
    ): SkillProviderListResult {
        $provider = $this->requireSkillProvider();
        $options = new SkillProviderCallOptions($token);

        try {
            $skills = $provider instanceof SkillProvider
                ? $provider->listSkills($options)
                : ($provider['listSkills'])($options);
        } catch (Throwable) {
            throw new JsonRpcException(-32603, 'Skill provider list failed');
        }

        if ($token->isCancellationRequested()) {
            throw new JsonRpcException(-32800, 'Skill provider list cancelled');
        }

        $normalized = [];
        foreach ($skills ?? [] as $skill) {
            if ($skill instanceof Arrayable) {
                $skill = $skill->toArray();
            }
            if (is_array($skill)) {
                $normalized[] = $skill;
            }
        }

        return new SkillProviderListResult($normalized);
    }

    /**
     * Handle the runtime's lazy session-scoped skill content request.
     */
    public function handleSkillProviderRead(
        SkillProviderReadRequest $request,
        CancellationToken $token,
    ): SkillProviderReadResult {
        $provider = $this->requireSkillProvider();
        $options = new SkillProviderCallOptions($token);

        try {
            $markdown = $provider instanceof SkillProvider
                ? $provider->readSkill($request->name, $options)
                : ($provider['readSkill'])($request->name, $options);
        } catch (Throwable) {
            throw new JsonRpcException(-32603, 'Skill provider read failed');
        }

        if ($token->isCancellationRequested()) {
            throw new JsonRpcException(-32800, 'Skill provider read cancelled');
        }

        if ($markdown !== null && ! is_string($markdown)) {
            throw new JsonRpcException(-32603, 'Skill provider read failed');
        }

        return new SkillProviderReadResult($markdown);
    }

    private function requireSkillProvider(): SkillProvider|array
    {
        if ($this->skillProvider === null) {
            throw new JsonRpcException(-32603, 'No skill provider configured for session');
        }

        return $this->skillProvider;
    }
}
