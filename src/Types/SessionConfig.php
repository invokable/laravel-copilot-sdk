<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ReasoningEffort;

/**
 * Configuration for creating a session.
 */
readonly class SessionConfig implements Arrayable
{
    public function __construct(
        /**
         * Optional custom session ID.
         * If not provided, server will generate one.
         */
        public ?string $sessionId = null,
        /**
         * Model to use for this session.
         */
        public ?string $model = null,
        /**
         * Reasoning effort level for models that support it.
         * Only valid for models where capabilities.supports.reasoningEffort is true.
         * Use client.listModels() to check supported values for each model.
         * Accepts either ReasoningEffort enum or string value.
         */
        public ReasoningEffort|string|null $reasoningEffort = null,
        /**
         * Override the default configuration directory location.
         * When specified, the session will use this directory for storing config and state.
         */
        public ?string $configDir = null,
        /**
         * Tools exposed to the CLI server.
         */
        public ?array $tools = null,
        /**
         * System message configuration.
         * Controls how the system prompt is constructed.
         */
        public SystemMessageConfig|array|null $systemMessage = null,
        /**
         * List of tool names to allow. When specified, only these tools will be available.
         * Takes precedence over excludedTools.
         */
        public ?array $availableTools = null,
        /**
         * List of tool names to disable. All other tools remain available.
         * Ignored if availableTools is specified.
         */
        public ?array $excludedTools = null,
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
         * Infinite session configuration for persistent workspaces and automatic compaction.
         * When enabled (default), sessions automatically manage context limits and persist state.
         * Set to `new InfiniteSessionConfig(enabled: false)` to disable.
         */
        public InfiniteSessionConfig|array|null $infiniteSessions = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        $systemMessage = null;
        if (isset($data['systemMessage'])) {
            $systemMessage = $data['systemMessage'] instanceof SystemMessageConfig
                ? $data['systemMessage']
                : SystemMessageConfig::fromArray($data['systemMessage']);
        }

        $provider = null;
        if (isset($data['provider'])) {
            $provider = $data['provider'] instanceof ProviderConfig
                ? $data['provider']
                : ProviderConfig::fromArray($data['provider']);
        }

        $infiniteSessions = null;
        if (isset($data['infiniteSessions'])) {
            $infiniteSessions = $data['infiniteSessions'] instanceof InfiniteSessionConfig
                ? $data['infiniteSessions']
                : InfiniteSessionConfig::fromArray($data['infiniteSessions']);
        }

        $hooks = null;
        if (isset($data['hooks'])) {
            $hooks = $data['hooks'] instanceof SessionHooks
                ? $data['hooks']
                : SessionHooks::fromArray($data['hooks']);
        }

        $reasoningEffort = null;
        if (isset($data['reasoningEffort'])) {
            $reasoningEffort = $data['reasoningEffort'] instanceof ReasoningEffort
                ? $data['reasoningEffort']
                : $data['reasoningEffort'];
        }

        return new self(
            sessionId: $data['sessionId'] ?? null,
            model: $data['model'] ?? null,
            reasoningEffort: $reasoningEffort,
            configDir: $data['configDir'] ?? null,
            tools: $data['tools'] ?? null,
            systemMessage: $systemMessage,
            availableTools: $data['availableTools'] ?? null,
            excludedTools: $data['excludedTools'] ?? null,
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
            infiniteSessions: $infiniteSessions,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $reasoningEffort = $this->reasoningEffort instanceof ReasoningEffort
            ? $this->reasoningEffort->value
            : $this->reasoningEffort;

        $systemMessage = $this->systemMessage instanceof SystemMessageConfig
            ? $this->systemMessage->toArray()
            : $this->systemMessage;

        $provider = $this->provider instanceof ProviderConfig
            ? $this->provider->toArray()
            : $this->provider;

        $infiniteSessions = $this->infiniteSessions instanceof InfiniteSessionConfig
            ? $this->infiniteSessions->toArray()
            : $this->infiniteSessions;

        $hooks = $this->hooks instanceof SessionHooks
            ? $this->hooks->toArray()
            : $this->hooks;

        return array_filter([
            'sessionId' => $this->sessionId,
            'model' => $this->model,
            'reasoningEffort' => $reasoningEffort,
            'configDir' => $this->configDir,
            'tools' => $this->tools,
            'systemMessage' => $systemMessage,
            'availableTools' => $this->availableTools,
            'excludedTools' => $this->excludedTools,
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
            'infiniteSessions' => $infiniteSessions,
        ], fn ($value) => $value !== null);
    }
}
