<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Hooks\SubagentStartHookInput;
use Revolution\Copilot\Types\Hooks\SubagentStopHookInput;
use Revolution\Copilot\Types\SessionHooks;
use Throwable;

/**
 * Manages session hooks registration and invocation.
 *
 * @internal
 */
trait HasHooks
{
    /**
     * Session hooks.
     */
    protected ?SessionHooks $hooks = null;

    /**
     * Register session hooks.
     *
     * @internal
     */
    public function registerHooks(SessionHooks|array|null $hooks): void
    {
        $this->hooks = $hooks instanceof SessionHooks
            ? $hooks
            : ($hooks !== null ? SessionHooks::fromArray($hooks) : null);
    }

    /**
     * Handle a hooks invocation.
     *
     * @internal
     */
    public function handleHooksInvoke(string $hookType, mixed $input): mixed
    {
        if ($this->hooks === null) {
            return null;
        }

        $handlerMap = [
            'preToolUse' => $this->hooks->onPreToolUse,
            'postToolUse' => $this->hooks->onPostToolUse,
            'postToolUseFailure' => $this->hooks->onPostToolUseFailure,
            'preMcpToolCall' => $this->hooks->onPreMcpToolCall,
            'userPromptSubmitted' => $this->hooks->onUserPromptSubmitted,
            'userPromptTransformed' => $this->hooks->onUserPromptTransformed,
            'sessionStart' => $this->hooks->onSessionStart,
            'sessionEnd' => $this->hooks->onSessionEnd,
            'errorOccurred' => $this->hooks->onErrorOccurred,
            'agentStop' => $this->hooks->onAgentStop,
            'subagentStart' => $this->hooks->onSubagentStart,
            'subagentStop' => $this->hooks->onSubagentStop,
        ];

        $handler = $handlerMap[$hookType] ?? null;

        if ($handler === null) {
            return null;
        }

        try {
            $input = match ($hookType) {
                'subagentStart' => is_array($input) ? SubagentStartHookInput::fromArray($input) : $input,
                'subagentStop' => is_array($input) ? SubagentStopHookInput::fromArray($input) : $input,
                default => $input,
            };
            $output = $handler($input, ['sessionId' => $this->sessionId]);

            return $output instanceof Arrayable ? $output->toArray() : $output;
        } catch (Throwable) {
            return null;
        }
    }
}
