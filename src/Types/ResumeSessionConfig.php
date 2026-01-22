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
         * Enable streaming of assistant message and reasoning chunks.
         * When true, ephemeral assistant.message_delta and assistant.reasoning_delta
         * events are sent as the response is generated.
         */
        public ?bool $streaming = null,
        /**
         * Handler for permission requests from the server.
         * When provided, the server will call this handler to request permission for operations.
         */
        public ?Closure $onPermissionRequest = null,
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
        $provider = null;
        if (isset($data['provider'])) {
            $provider = $data['provider'] instanceof ProviderConfig
                ? $data['provider']
                : ProviderConfig::fromArray($data['provider']);
        }

        return new self(
            tools: $data['tools'] ?? null,
            provider: $provider,
            streaming: $data['streaming'] ?? null,
            onPermissionRequest: $data['onPermissionRequest'] ?? null,
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
        $provider = $this->provider instanceof ProviderConfig
            ? $this->provider->toArray()
            : $this->provider;

        return array_filter([
            'tools' => $this->tools,
            'provider' => $provider,
            'streaming' => $this->streaming,
            'onPermissionRequest' => $this->onPermissionRequest,
            'mcpServers' => $this->mcpServers,
            'customAgents' => $this->customAgents,
            'skillDirectories' => $this->skillDirectories,
            'disabledSkills' => $this->disabledSkills,
        ], fn ($value) => $value !== null);
    }
}
