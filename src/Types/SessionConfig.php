<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Enums\RemoteSessionMode;
use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverride;

/**
 * Configuration for creating a session.
 */
readonly class SessionConfig implements Arrayable
{
    /**
     * @param  ?string  $sessionId  Optional custom session ID. If not provided, server will generate one.
     * @param  ?string  $clientName  Client name to identify the application using the SDK.
     *                               Included in the User-Agent header for API requests.
     * @param  ?string  $model  Model to use for this session
     * @param  ReasoningEffort|string|null  $reasoningEffort  Reasoning effort level for models that support it.
     *                                                        Only valid for models where capabilities.supports.reasoningEffort is true.
     *                                                        Use client.listModels() to check supported values for each model.
     *                                                        Accepts either ReasoningEffort enum or string value.
     * @param  ?string  $reasoningSummary  Reasoning summary mode for models that support configurable reasoning summaries.
     *                                     Use "none" to suppress summary output regardless of whether reasoning is enabled.
     * @param  ?string  $contextTier  Context window tier ("default" or "long_context"). Use "long_context" to pin
     *                                the session to the long-context tier when supported.
     * @param  ModelCapabilitiesOverride|array|null  $modelCapabilities  Per-property overrides for model capabilities, deep-merged over runtime defaults.
     * @param  ?string  $configDir  Override the default configuration directory location.
     *                              When specified, the session will use this directory for storing config and state.
     *                              Deprecated: use $configDirectory instead.
     * @param  ?string  $configDirectory  Override the default configuration directory location.
     *                                    When specified, the session will use this directory for storing config and state.
     * @param  ?bool  $enableConfigDiscovery  When true, automatically discovers MCP server configurations
     *                                        (e.g. `.mcp.json`, `.vscode/mcp.json`) and skill directories from the
     *                                        working directory and merges them with any explicitly provided
     *                                        `mcpServers` and `skillDirectories`, with explicit values taking
     *                                        precedence on name collision. Custom instruction files are always
     *                                        loaded regardless of this setting.
     * @param  ?array  $tools  Tools exposed to the CLI server
     * @param  ?array  $commands  Slash commands registered for this session.
     *                            When the CLI has a TUI, each command appears as `/name` for the user to invoke.
     *                            Each entry should have 'name', 'handler', and optionally 'description'.
     * @param  SystemMessageConfig|array|null  $systemMessage  System message configuration. Controls how the system prompt is constructed.
     * @param  ?array  $availableTools  List of source-qualified tool filters to allow. When specified, only these tools will be available.
     *                                  Examples: `builtin:*`, `builtin:bash`, `mcp:*`, `mcp:github-read_issue`, `custom:*`.
     * @param  ?array  $excludedTools  List of source-qualified tool filters to disable. When both lists are set,
     *                                 excludedTools wins (`toolFilterPrecedence: "excluded"`).
     * @param  ProviderConfig|array|null  $provider  Custom provider configuration (BYOK - Bring Your Own Key).
     *                                               When specified, uses the provided API endpoint instead of the Copilot API.
     * @param  ?Closure  $onPermissionRequest  Handler for permission requests from the server.
     *                                         When provided, the server will call this handler to request permission for operations.
     * @param  ?Closure  $onUserInputRequest  Handler for user input requests from the agent.
     *                                        When provided, enables the ask_user tool allowing the agent to ask questions.
     * @param  ?Closure  $onElicitationRequest  Handler for elicitation requests from the agent.
     *                                          When provided, the server calls back to this client for form-based UI dialogs.
     *                                          Also enables the `elicitation` capability on the session.
     * @param  ?Closure  $onExitPlanModeRequest  Handler for exit-plan-mode requests from the agent.
     *                                           When provided, enables `exitPlanMode.request` callbacks.
     * @param  ?Closure  $onAutoModeSwitchRequest  Handler for auto-mode-switch requests from the agent.
     *                                             When provided, enables `autoModeSwitch.request` callbacks.
     * @param  ?bool  $enableSessionTelemetry  Enables or disables internal session telemetry.
     *                                         When false, disables session telemetry. When omitted or true,
     *                                         telemetry is enabled for GitHub-authenticated sessions.
     *                                         When a custom provider (BYOK) is configured, session telemetry
     *                                         is always disabled regardless of this setting.
     * @param  SessionHooks|array|null  $hooks  Hook handlers for intercepting session lifecycle events.
     *                                          When provided, enables hooks callback allowing custom logic at various points.
     * @param  ?string  $workingDirectory  Working directory for the session. Tool operations will be relative to this directory.
     * @param  ?bool  $streaming  Enable streaming of assistant message and reasoning chunks.
     *                            When true, ephemeral assistant.message_delta and assistant.reasoning_delta
     *                            events are sent as the response is generated.
     * @param  ?bool  $includeSubAgentStreamingEvents  Include sub-agent streaming events in the event stream.
     *                                                 When true, streaming delta events from sub-agents (e.g., assistant.message_delta,
     *                                                 assistant.reasoning_delta, assistant.streaming_delta with agentId set)
     *                                                 are forwarded to this connection. When false, only non-streaming sub-agent
     *                                                 events and subagent.* lifecycle events are forwarded; streaming deltas from
     *                                                 sub-agents are suppressed. Defaults to true.
     * @param  ?array  $mcpServers  MCP server configurations for the session. Keys are server names, values are server configurations.
     * @param  ?array  $customAgents  Custom agent configurations for the session
     * @param  ?array  $defaultAgent  Configuration for the default agent (the built-in agent that handles
     *                                turns when no custom agent is selected). Use `excludedTools` to hide
     *                                specific tools from the default agent while keeping them available to
     *                                custom sub-agents. Example: `['excludedTools' => ['tool_name']]`
     * @param  ?string  $agent  Name of the custom agent to activate when the session starts.
     *                          Must match the `name` of one of the agents in `customAgents`.
     *                          Equivalent to calling `session.rpc.agent.select({ name })` after creation.
     * @param  ?array  $skillDirectories  Directories to load skills from
     * @param  ?array  $pluginDirectories  Local filesystem paths to Open Plugins-format directories to load for this session.
     * @param  ?array  $instructionDirectories  Additional directories to search for custom instruction files.
     * @param  ?array  $disabledSkills  List of skill names to disable
     * @param  ?bool  $skipCustomInstructions  When true, custom instruction files are not loaded.
     * @param  ?bool  $customAgentsLocalOnly  When true, only local custom agents are considered.
     * @param  ?bool  $suppressCustomAgentPrompt  When true, the selected custom agent's prompt is not injected into the user message (skill context is still injected). Used by automation triggers where the agent prompt is already in the problem statement.
     * @param  ?bool  $coauthorEnabled  Enables co-author integration for this session.
     * @param  ?bool  $manageScheduleEnabled  Enables schedule management tools for this session.
     * @param  InfiniteSessionConfig|array|null  $infiniteSessions  Infinite session configuration for persistent workspaces and automatic compaction.
     *                                                              When enabled (default), sessions automatically manage context limits and persist state.
     *                                                              Set to `new InfiniteSessionConfig(enabled: false)` to disable.
     * @param  ?string  $gitHubToken  GitHub token for per-session authentication.
     *                                When provided, the runtime resolves this token into a full GitHub identity
     *                                (login, Copilot plan, endpoints) and stores it on the session.
     *                                This enables multitenancy — different sessions can have different GitHub identities.
     * @param  RemoteSessionMode|string|null  $remoteSession  Per-session remote behavior control:
     *                                                        - `"off"` — local only, no remote export (default)
     *                                                        - `"export"` — export session events to GitHub without enabling remote steering
     *                                                        - `"on"` — export to GitHub AND enable remote steering
     * @param  CloudSessionOptions|array|null  $cloud  Creates a remote session in the cloud instead of a local session.
     *                                                 The optional repository is associated with the cloud session.
     * @param  ?array  $canvases  Canvases contributed by this session participant. The declaring connection becomes
     *                            the live provider for canvas operations.
     *                            Experimental: this is part of an experimental API and may change or be removed.
     * @param  ?bool  $requestCanvasRenderer  Renderer-side opt-in: when true, the runtime surfaces canvas agent tools
     *                                        to the model for this connection. Default off so SDK callers that cannot
     *                                        display canvases stay clean.
     *                                        Experimental: this is part of an experimental API and may change or be removed.
     * @param  ?bool  $requestExtensions  Extension surface opt-in: when true, the runtime wires extension management
     *                                    tools onto the session for this connection. Default off so callers that do
     *                                    not expose extensions stay clean.
     *                                    Experimental: this is part of an experimental API and may change or be removed.
     * @param  ExtensionInfo|array|null  $extensionInfo  Stable extension identity for canvas providers on this connection.
     *                                                   When set, the runtime uses `${source}:${name}` as the agent-facing
     *                                                   extension id.
     *                                                   Experimental: this is part of an experimental API and may change or be removed.
     * @param  ?Closure  $onEvent  Optional event handler registered on the session before the session.create RPC is issued.
     *                             This guarantees that early events emitted by the CLI during session creation (e.g. session.start)
     *                             are delivered to the handler.
     *                             Equivalent to calling `$session->on($handler)` immediately after creation, but executes
     *                             earlier in the lifecycle so no events are missed.
     * @param  LargeToolOutputConfig|array|null  $largeOutput  Configuration for handling large tool outputs.
     *                                                         When a tool produces output exceeding the configured size, the output is
     *                                                         written to a temp file and a reference is returned to the model.
     * @param  ?string  $extensionSdkPath  Optional override path to a `copilot-sdk/` folder to inject into extension subprocesses.
     * @param  ?bool  $enableMcpApps  Enable MCP Apps (SEP-1865) UI passthrough on this session. Experimental.
     * @param  ?string  $mcpOAuthTokenStorage  Controls how MCP OAuth tokens are stored ("persistent" or "in-memory").
     * @param  ?bool  $skipEmbeddingRetrieval  When true, skips embedding-based retrieval for this session.
     * @param  ?string  $embeddingCacheStorage  Controls how the embedding cache is stored ("persistent" or "in-memory").
     * @param  ?string  $organizationCustomInstructions  Organization-level custom instructions to include in the system prompt.
     * @param  ?bool  $enableOnDemandInstructionDiscovery  When true, enables on-demand discovery of instruction files.
     * @param  ?bool  $enableFileHooks  When true, enables loading of file-based hooks from `.github/hooks/`.
     * @param  ?bool  $enableHostGitOperations  When true, enables git operations on the host filesystem.
     * @param  ?bool  $enableSessionStore  When true, enables the cross-session store for search and retrieval.
     * @param  ?bool  $enableSkills  When true, enables skill loading.
     * @param  ?string  $displayPrompt  If provided, shown in the timeline instead of `prompt`.
     */
    public function __construct(
        public ?string $sessionId = null,
        public ?string $clientName = null,
        public ?string $model = null,
        public ReasoningEffort|string|null $reasoningEffort = null,
        public ?string $reasoningSummary = null,
        public ?string $contextTier = null,
        public ModelCapabilitiesOverride|array|null $modelCapabilities = null,
        public ?string $configDir = null,
        public ?string $configDirectory = null,
        public ?bool $enableConfigDiscovery = null,
        public ?array $tools = null,
        public ?array $commands = null,
        public SystemMessageConfig|array|null $systemMessage = null,
        public ?array $availableTools = null,
        public ?array $excludedTools = null,
        public ProviderConfig|array|null $provider = null,
        public ?Closure $onPermissionRequest = null,
        public ?Closure $onUserInputRequest = null,
        public ?Closure $onElicitationRequest = null,
        public ?Closure $onExitPlanModeRequest = null,
        public ?Closure $onAutoModeSwitchRequest = null,
        public ?bool $enableSessionTelemetry = null,
        public SessionHooks|array|null $hooks = null,
        public ?string $workingDirectory = null,
        public ?bool $streaming = null,
        public ?bool $includeSubAgentStreamingEvents = null,
        public ?array $mcpServers = null,
        public ?array $customAgents = null,
        public ?array $defaultAgent = null,
        public ?string $agent = null,
        public ?array $skillDirectories = null,
        public ?array $pluginDirectories = null,
        public ?array $instructionDirectories = null,
        public ?array $disabledSkills = null,
        public InfiniteSessionConfig|array|null $infiniteSessions = null,
        public ?string $gitHubToken = null,
        public RemoteSessionMode|string|null $remoteSession = null,
        public CloudSessionOptions|array|null $cloud = null,
        public ?array $canvases = null,
        public ?bool $requestCanvasRenderer = null,
        public ?bool $requestExtensions = null,
        public ExtensionInfo|array|null $extensionInfo = null,
        public ?Closure $onEvent = null,
        public ?bool $skipCustomInstructions = null,
        public ?bool $customAgentsLocalOnly = null,
        public ?bool $suppressCustomAgentPrompt = null,
        public ?bool $coauthorEnabled = null,
        public ?bool $manageScheduleEnabled = null,
        public LargeToolOutputConfig|array|null $largeOutput = null,
        public ?string $extensionSdkPath = null,
        public ?bool $enableMcpApps = null,
        public ?string $mcpOAuthTokenStorage = null,
        public ?bool $skipEmbeddingRetrieval = null,
        public ?string $embeddingCacheStorage = null,
        public ?string $organizationCustomInstructions = null,
        public ?bool $enableOnDemandInstructionDiscovery = null,
        public ?bool $enableFileHooks = null,
        public ?bool $enableHostGitOperations = null,
        public ?bool $enableSessionStore = null,
        public ?bool $enableSkills = null,
        public ?string $displayPrompt = null,
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

        $modelCapabilities = null;
        if (isset($data['modelCapabilities'])) {
            $modelCapabilities = $data['modelCapabilities'] instanceof ModelCapabilitiesOverride
                ? $data['modelCapabilities']
                : ModelCapabilitiesOverride::fromArray($data['modelCapabilities']);
        }

        $cloud = null;
        if (isset($data['cloud'])) {
            $cloud = $data['cloud'] instanceof CloudSessionOptions
                ? $data['cloud']
                : CloudSessionOptions::fromArray($data['cloud']);
        }

        $extensionInfo = null;
        if (isset($data['extensionInfo'])) {
            $extensionInfo = $data['extensionInfo'] instanceof ExtensionInfo
                ? $data['extensionInfo']
                : ExtensionInfo::fromArray($data['extensionInfo']);
        }

        $largeOutput = null;
        if (isset($data['largeOutput'])) {
            $largeOutput = $data['largeOutput'] instanceof LargeToolOutputConfig
                ? $data['largeOutput']
                : LargeToolOutputConfig::fromArray($data['largeOutput']);
        }

        return new self(
            sessionId: $data['sessionId'] ?? null,
            clientName: $data['clientName'] ?? null,
            model: $data['model'] ?? null,
            reasoningEffort: $data['reasoningEffort'] ?? null,
            reasoningSummary: $data['reasoningSummary'] ?? null,
            contextTier: $data['contextTier'] ?? null,
            modelCapabilities: $modelCapabilities,
            configDir: $data['configDir'] ?? null,
            configDirectory: $data['configDirectory'] ?? null,
            enableConfigDiscovery: $data['enableConfigDiscovery'] ?? null,
            tools: $data['tools'] ?? null,
            commands: $data['commands'] ?? null,
            systemMessage: $systemMessage,
            availableTools: $data['availableTools'] ?? null,
            excludedTools: $data['excludedTools'] ?? null,
            provider: $provider,
            onPermissionRequest: $data['onPermissionRequest'] ?? null,
            onUserInputRequest: $data['onUserInputRequest'] ?? null,
            onElicitationRequest: $data['onElicitationRequest'] ?? null,
            onExitPlanModeRequest: $data['onExitPlanModeRequest'] ?? $data['onExitPlanMode'] ?? null,
            onAutoModeSwitchRequest: $data['onAutoModeSwitchRequest'] ?? $data['onAutoModeSwitch'] ?? null,
            enableSessionTelemetry: $data['enableSessionTelemetry'] ?? null,
            hooks: $hooks,
            workingDirectory: $data['workingDirectory'] ?? null,
            streaming: $data['streaming'] ?? null,
            includeSubAgentStreamingEvents: $data['includeSubAgentStreamingEvents'] ?? null,
            mcpServers: $data['mcpServers'] ?? null,
            customAgents: $data['customAgents'] ?? null,
            defaultAgent: $data['defaultAgent'] ?? null,
            agent: $data['agent'] ?? null,
            skillDirectories: $data['skillDirectories'] ?? null,
            pluginDirectories: $data['pluginDirectories'] ?? null,
            instructionDirectories: $data['instructionDirectories'] ?? null,
            disabledSkills: $data['disabledSkills'] ?? null,
            skipCustomInstructions: $data['skipCustomInstructions'] ?? null,
            customAgentsLocalOnly: $data['customAgentsLocalOnly'] ?? null,
            suppressCustomAgentPrompt: $data['suppressCustomAgentPrompt'] ?? null,
            coauthorEnabled: $data['coauthorEnabled'] ?? null,
            manageScheduleEnabled: $data['manageScheduleEnabled'] ?? null,
            infiniteSessions: $infiniteSessions,
            gitHubToken: $data['gitHubToken'] ?? null,
            remoteSession: $data['remoteSession'] ?? null,
            cloud: $cloud,
            canvases: $data['canvases'] ?? null,
            requestCanvasRenderer: $data['requestCanvasRenderer'] ?? null,
            requestExtensions: $data['requestExtensions'] ?? null,
            extensionInfo: $extensionInfo,
            onEvent: $data['onEvent'] ?? null,
            largeOutput: $largeOutput,
            extensionSdkPath: $data['extensionSdkPath'] ?? null,
            enableMcpApps: $data['enableMcpApps'] ?? null,
            mcpOAuthTokenStorage: $data['mcpOAuthTokenStorage'] ?? null,
            skipEmbeddingRetrieval: $data['skipEmbeddingRetrieval'] ?? null,
            embeddingCacheStorage: $data['embeddingCacheStorage'] ?? null,
            organizationCustomInstructions: $data['organizationCustomInstructions'] ?? null,
            enableOnDemandInstructionDiscovery: $data['enableOnDemandInstructionDiscovery'] ?? null,
            enableFileHooks: $data['enableFileHooks'] ?? null,
            enableHostGitOperations: $data['enableHostGitOperations'] ?? null,
            enableSessionStore: $data['enableSessionStore'] ?? null,
            enableSkills: $data['enableSkills'] ?? null,
            displayPrompt: $data['displayPrompt'] ?? null,
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

        $modelCapabilities = $this->modelCapabilities instanceof ModelCapabilitiesOverride
            ? $this->modelCapabilities->toArray()
            : $this->modelCapabilities;

        $remoteSession = $this->remoteSession instanceof RemoteSessionMode
            ? $this->remoteSession->value
            : $this->remoteSession;

        $cloud = $this->cloud instanceof CloudSessionOptions
            ? $this->cloud->toArray()
            : $this->cloud;

        $extensionInfo = $this->extensionInfo instanceof ExtensionInfo
            ? $this->extensionInfo->toArray()
            : $this->extensionInfo;

        $largeOutput = $this->largeOutput instanceof LargeToolOutputConfig
            ? $this->largeOutput->toArray()
            : $this->largeOutput;

        return array_filter([
            'sessionId' => $this->sessionId,
            'clientName' => $this->clientName,
            'model' => $this->model,
            'reasoningEffort' => $reasoningEffort,
            'reasoningSummary' => $this->reasoningSummary,
            'contextTier' => $this->contextTier,
            'modelCapabilities' => $modelCapabilities,
            'configDir' => $this->configDir,
            'configDirectory' => $this->configDirectory,
            'enableConfigDiscovery' => $this->enableConfigDiscovery,
            'tools' => $this->tools,
            'commands' => $this->commands,
            'systemMessage' => $systemMessage,
            'availableTools' => $this->availableTools,
            'excludedTools' => $this->excludedTools,
            'provider' => $provider,
            'onPermissionRequest' => $this->onPermissionRequest,
            'onUserInputRequest' => $this->onUserInputRequest,
            'onElicitationRequest' => $this->onElicitationRequest,
            'onExitPlanModeRequest' => $this->onExitPlanModeRequest,
            'onAutoModeSwitchRequest' => $this->onAutoModeSwitchRequest,
            'enableSessionTelemetry' => $this->enableSessionTelemetry,
            'hooks' => $hooks,
            'workingDirectory' => $this->workingDirectory,
            'streaming' => $this->streaming,
            'includeSubAgentStreamingEvents' => $this->includeSubAgentStreamingEvents,
            'mcpServers' => $this->mcpServers,
            'customAgents' => $this->customAgents,
            'defaultAgent' => $this->defaultAgent,
            'agent' => $this->agent,
            'skillDirectories' => $this->skillDirectories,
            'pluginDirectories' => $this->pluginDirectories,
            'instructionDirectories' => $this->instructionDirectories,
            'disabledSkills' => $this->disabledSkills,
            'skipCustomInstructions' => $this->skipCustomInstructions,
            'customAgentsLocalOnly' => $this->customAgentsLocalOnly,
            'suppressCustomAgentPrompt' => $this->suppressCustomAgentPrompt,
            'coauthorEnabled' => $this->coauthorEnabled,
            'manageScheduleEnabled' => $this->manageScheduleEnabled,
            'infiniteSessions' => $infiniteSessions,
            'gitHubToken' => $this->gitHubToken,
            'remoteSession' => $remoteSession,
            'cloud' => $cloud,
            'canvases' => $this->canvases,
            'requestCanvasRenderer' => $this->requestCanvasRenderer,
            'requestExtensions' => $this->requestExtensions,
            'extensionInfo' => $extensionInfo,
            'onEvent' => $this->onEvent,
            'largeOutput' => $largeOutput,
            'extensionSdkPath' => $this->extensionSdkPath,
            'enableMcpApps' => $this->enableMcpApps,
            'mcpOAuthTokenStorage' => $this->mcpOAuthTokenStorage,
            'skipEmbeddingRetrieval' => $this->skipEmbeddingRetrieval,
            'embeddingCacheStorage' => $this->embeddingCacheStorage,
            'organizationCustomInstructions' => $this->organizationCustomInstructions,
            'enableOnDemandInstructionDiscovery' => $this->enableOnDemandInstructionDiscovery,
            'enableFileHooks' => $this->enableFileHooks,
            'enableHostGitOperations' => $this->enableHostGitOperations,
            'enableSessionStore' => $this->enableSessionStore,
            'enableSkills' => $this->enableSkills,
            'displayPrompt' => $this->displayPrompt,
        ], fn ($value) => $value !== null);
    }
}
