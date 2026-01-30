<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Configuration for resuming a session.
 */
readonly class ResumeSessionConfig implements Arrayable
{
    public function __construct(
        /**
         * Tools exposed to the CLI server.
         */
        public ?array $tools = null,
        /**
         * Custom provider configuration (BYOK - Bring Your Own Key).
         * When specified, uses the provided API endpoint instead of the Copilot API.
         */
        public ProviderConfig|array|null $provider = null,
        /**
         * Handler for permission requests from the server.
         * When provided, the server will call this handler to request permission for operations.
         */
        public ?Closure $onPermissionRequest = null,
        /**
         * Handler for user input requests from the agent.
         * When provided, enables the ask_user tool allowing the agent to ask questions.
         */
        public ?Closure $onUserInputRequest = null,
        /**
         * Hook handlers for intercepting session lifecycle events.
         * When provided, enables hooks callback allowing custom logic at various points.
         */
        public SessionHooks|array|null $hooks = null,
        /**
         * Working directory for the session.
         * Tool operations will be relative to this directory.
         */
        public ?string $workingDirectory = null,
        /**
         * Enable streaming of assistant message and reasoning chunks.
         * When true, ephemeral assistant.message_delta and assistant.reasoning_delta
         * events are sent as the response is generated.
         */
        public ?bool $streaming = null,
        /**
         * MCP server configurations for the session.
         * Keys are server names, values are server configurations.
         */
        public ?array $mcpServers = null,
        /**
         * Custom agent configurations for the session.
         */
        public ?array $customAgents = null,
        /**
         * Directories to load skills from.
         */
        public ?array $skillDirectories = null,
        /**
         * List of skill names to disable.
         */
        public ?array $disabledSkills = null,
        /**
         * When true, skips emitting the session.resume event.
         * Useful for reconnecting to a session without triggering resume-related side effects.
         *
         * @default false
         */
        public ?bool $disableResume = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        $provider = null;
        if (isset($data['provider'])) {
            $provider = $data['provider'] instanceof ProviderConfig
                ? $data['provider']
                : ProviderConfig::fromArray($data['provider']);
        }

        $hooks = null;
        if (isset($data['hooks'])) {
            $hooks = $data['hooks'] instanceof SessionHooks
                ? $data['hooks']
                : SessionHooks::fromArray($data['hooks']);
        }

        return new self(
            tools: $data['tools'] ?? null,
            provider: $provider,
            onPermissionRequest: $data['onPermissionRequest'] ?? null,
            onUserInputRequest: $data['onUserInputRequest'] ?? null,
            hooks: $hooks,
            workingDirectory: $data['workingDirectory'] ?? null,
            streaming: $data['streaming'] ?? null,
            mcpServers: $data['mcpServers'] ?? null,
            customAgents: $data['customAgents'] ?? null,
            skillDirectories: $data['skillDirectories'] ?? null,
            disabledSkills: $data['disabledSkills'] ?? null,
            disableResume: $data['disableResume'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $provider = $this->provider instanceof ProviderConfig
            ? $this->provider->toArray()
            : $this->provider;

        $hooks = $this->hooks instanceof SessionHooks
            ? $this->hooks->toArray()
            : $this->hooks;

        return array_filter([
            'tools' => $this->tools,
            'provider' => $provider,
            'onPermissionRequest' => $this->onPermissionRequest,
            'onUserInputRequest' => $this->onUserInputRequest,
            'hooks' => $hooks,
            'workingDirectory' => $this->workingDirectory,
            'streaming' => $this->streaming,
            'mcpServers' => $this->mcpServers,
            'customAgents' => $this->customAgents,
            'skillDirectories' => $this->skillDirectories,
            'disabledSkills' => $this->disabledSkills,
            'disableResume' => $this->disableResume,
        ], fn ($value) => $value !== null);
    }
}
