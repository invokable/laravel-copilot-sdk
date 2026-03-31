<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ReasoningEffort;

/**
 * Configuration for resuming a session.
 */
readonly class ResumeSessionConfig implements Arrayable
{
    /**
     * @param  ?string  $clientName  Client name to identify the application using the SDK.
     *                               Included in the User-Agent header for API requests.
     * @param  ?string  $model  Model to use for this session
     * @param  ReasoningEffort|string|null  $reasoningEffort  Reasoning effort level for models that support it.
     *                                                        Only valid for models where capabilities.supports.reasoningEffort is true.
     *                                                        Use client.listModels() to check supported values for each model.
     *                                                        Accepts either ReasoningEffort enum or string value.
     * @param  ?string  $configDir  Override the default configuration directory location.
     *                              When specified, the session will use this directory for storing config and state.
     * @param  ?array  $tools  Tools exposed to the CLI server
     * @param  ?array  $commands  Slash commands registered for this session.
     *                            When the CLI has a TUI, each command appears as `/name` for the user to invoke.
     *                            Each entry should have 'name', 'handler', and optionally 'description'.
     * @param  SystemMessageConfig|array|null  $systemMessage  System message configuration. Controls how the system prompt is constructed.
     * @param  ?array  $availableTools  List of tool names to allow. When specified, only these tools will be available.
     *                                  Takes precedence over excludedTools.
     * @param  ?array  $excludedTools  List of tool names to disable. All other tools remain available.
     *                                 Ignored if availableTools is specified.
     * @param  ProviderConfig|array|null  $provider  Custom provider configuration (BYOK - Bring Your Own Key).
     *                                               When specified, uses the provided API endpoint instead of the Copilot API.
     * @param  ?Closure  $onPermissionRequest  Handler for permission requests from the server.
     *                                         When provided, the server will call this handler to request permission for operations.
     * @param  ?Closure  $onUserInputRequest  Handler for user input requests from the agent.
     *                                        When provided, enables the ask_user tool allowing the agent to ask questions.
     * @param  ?Closure  $onElicitationRequest  Handler for elicitation requests from the agent.
     *                                          When provided, the server calls back to this client for form-based UI dialogs.
     *                                          Also enables the `elicitation` capability on the session.
     * @param  SessionHooks|array|null  $hooks  Hook handlers for intercepting session lifecycle events.
     *                                          When provided, enables hooks callback allowing custom logic at various points.
     * @param  ?string  $workingDirectory  Working directory for the session. Tool operations will be relative to this directory.
     * @param  ?bool  $streaming  Enable streaming of assistant message and reasoning chunks.
     *                            When true, ephemeral assistant.message_delta and assistant.reasoning_delta
     *                            events are sent as the response is generated.
     * @param  ?array  $mcpServers  MCP server configurations for the session. Keys are server names, values are server configurations.
     * @param  ?array  $customAgents  Custom agent configurations for the session
     * @param  ?string  $agent  Name of the custom agent to activate when the session resumes.
     *                          Must match the `name` of one of the agents in `customAgents`.
     * @param  ?array  $skillDirectories  Directories to load skills from
     * @param  ?array  $disabledSkills  List of skill names to disable
     * @param  InfiniteSessionConfig|array|null  $infiniteSessions  Infinite session configuration for persistent workspaces and automatic compaction.
     *                                                              When enabled (default), sessions automatically manage context limits and persist state.
     *                                                              Set to `new InfiniteSessionConfig(enabled: false)` to disable.
     * @param  ?bool  $disableResume  When true, skips emitting the session.resume event.
     *                                Useful for reconnecting to a session without triggering resume-related side effects.
     * @param  ?Closure  $onEvent  Optional event handler registered on the session before the session.resume RPC is issued.
     *                             This guarantees that early events emitted by the CLI during session resumption
     *                             are delivered to the handler.
     *                             Equivalent to calling `$session->on($handler)` immediately after resumption, but executes
     *                             earlier in the lifecycle so no events are missed.
     */
    public function __construct(
        public ?string $clientName = null,
        public ?string $model = null,
        public ReasoningEffort|string|null $reasoningEffort = null,
        public ?string $configDir = null,
        public ?array $tools = null,
        public ?array $commands = null,
        public SystemMessageConfig|array|null $systemMessage = null,
        public ?array $availableTools = null,
        public ?array $excludedTools = null,
        public ProviderConfig|array|null $provider = null,
        public ?Closure $onPermissionRequest = null,
        public ?Closure $onUserInputRequest = null,
        public ?Closure $onElicitationRequest = null,
        public SessionHooks|array|null $hooks = null,
        public ?string $workingDirectory = null,
        public ?bool $streaming = null,
        public ?array $mcpServers = null,
        public ?array $customAgents = null,
        public ?string $agent = null,
        public ?array $skillDirectories = null,
        public ?array $disabledSkills = null,
        public InfiniteSessionConfig|array|null $infiniteSessions = null,
        public ?bool $disableResume = null,
        public ?Closure $onEvent = null,
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

        return new self(
            clientName: $data['clientName'] ?? null,
            model: $data['model'] ?? null,
            reasoningEffort: $data['reasoningEffort'] ?? null,
            configDir: $data['configDir'] ?? null,
            tools: $data['tools'] ?? null,
            commands: $data['commands'] ?? null,
            systemMessage: $systemMessage,
            availableTools: $data['availableTools'] ?? null,
            excludedTools: $data['excludedTools'] ?? null,
            provider: $provider,
            onPermissionRequest: $data['onPermissionRequest'] ?? null,
            onUserInputRequest: $data['onUserInputRequest'] ?? null,
            onElicitationRequest: $data['onElicitationRequest'] ?? null,
            hooks: $hooks,
            workingDirectory: $data['workingDirectory'] ?? null,
            streaming: $data['streaming'] ?? null,
            mcpServers: $data['mcpServers'] ?? null,
            customAgents: $data['customAgents'] ?? null,
            agent: $data['agent'] ?? null,
            skillDirectories: $data['skillDirectories'] ?? null,
            disabledSkills: $data['disabledSkills'] ?? null,
            infiniteSessions: $infiniteSessions,
            disableResume: $data['disableResume'] ?? null,
            onEvent: $data['onEvent'] ?? null,
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
            'clientName' => $this->clientName,
            'model' => $this->model,
            'reasoningEffort' => $reasoningEffort,
            'configDir' => $this->configDir,
            'tools' => $this->tools,
            'commands' => $this->commands,
            'systemMessage' => $systemMessage,
            'availableTools' => $this->availableTools,
            'excludedTools' => $this->excludedTools,
            'provider' => $provider,
            'onPermissionRequest' => $this->onPermissionRequest,
            'onUserInputRequest' => $this->onUserInputRequest,
            'onElicitationRequest' => $this->onElicitationRequest,
            'hooks' => $hooks,
            'workingDirectory' => $this->workingDirectory,
            'streaming' => $this->streaming,
            'mcpServers' => $this->mcpServers,
            'customAgents' => $this->customAgents,
            'agent' => $this->agent,
            'skillDirectories' => $this->skillDirectories,
            'disabledSkills' => $this->disabledSkills,
            'infiniteSessions' => $infiniteSessions,
            'disableResume' => $this->disableResume,
            'onEvent' => $this->onEvent,
        ], fn ($value) => $value !== null);
    }
}
