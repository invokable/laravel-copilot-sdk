<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

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

        return new self(
            sessionId: $data['sessionId'] ?? null,
            model: $data['model'] ?? null,
            configDir: $data['configDir'] ?? null,
            tools: $data['tools'] ?? null,
            systemMessage: $systemMessage,
            availableTools: $data['availableTools'] ?? null,
            excludedTools: $data['excludedTools'] ?? null,
            provider: $provider,
            onPermissionRequest: $data['onPermissionRequest'] ?? null,
            streaming: $data['streaming'] ?? null,
            mcpServers: $data['mcpServers'] ?? null,
            customAgents: $data['customAgents'] ?? null,
            skillDirectories: $data['skillDirectories'] ?? null,
            disabledSkills: $data['disabledSkills'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $systemMessage = $this->systemMessage instanceof SystemMessageConfig
            ? $this->systemMessage->toArray()
            : $this->systemMessage;

        $provider = $this->provider instanceof ProviderConfig
            ? $this->provider->toArray()
            : $this->provider;

        return array_filter([
            'sessionId' => $this->sessionId,
            'model' => $this->model,
            'configDir' => $this->configDir,
            'tools' => $this->tools,
            'systemMessage' => $systemMessage,
            'availableTools' => $this->availableTools,
            'excludedTools' => $this->excludedTools,
            'provider' => $provider,
            'onPermissionRequest' => $this->onPermissionRequest,
            'streaming' => $this->streaming,
            'mcpServers' => $this->mcpServers,
            'customAgents' => $this->customAgents,
            'skillDirectories' => $this->skillDirectories,
            'disabledSkills' => $this->disabledSkills,
        ], fn ($value) => $value !== null);
    }
}
