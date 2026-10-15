<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * In-process MCP reload configuration.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpReloadConfig implements Arrayable
{
    /**
     * @param  array<string, McpServerValue|array<string, mixed>>  $mcpServers
     * @param  array<string>|null  $disabledServers  Explicit per-session disables.
     * @param  array<string>|null  $enabledServers
     * @param  array<string>|null  $cliEnabledServers  Server names enabled via `--enable-mcp-server`.
     * @param  array<string>|null  $configuredDisabledServers  Caller-resolved settings disables. Kept separate from
     *                                                         disabledServers; the runtime applies settings disables below
     *                                                         CLI enables and explicit session disables.
     */
    public function __construct(
        public array $mcpServers,
        public ?array $disabledServers = null,
        public ?array $enabledServers = null,
        public ?array $cliEnabledServers = null,
        public ?array $configuredDisabledServers = null,
        public ?bool $mcp3pEnabled = null,
        public ?bool $includeWorkspaceSources = null,
        public mixed $configFilter = null,
        public mixed $githubMcpToolOptions = null,
        public ?bool $githubMcpUserOverride = null,
        public mixed $secretStore = null,
        public ?string $activeGitHubToken = null,
        public ?bool $useCachedToolSnapshots = null,
        public ?bool $forceRestart = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mcpServers: $data['mcpServers'] ?? [],
            disabledServers: $data['disabledServers'] ?? null,
            enabledServers: $data['enabledServers'] ?? null,
            cliEnabledServers: $data['cliEnabledServers'] ?? null,
            configuredDisabledServers: $data['configuredDisabledServers'] ?? null,
            mcp3pEnabled: $data['mcp3pEnabled'] ?? null,
            includeWorkspaceSources: $data['includeWorkspaceSources'] ?? null,
            configFilter: $data['configFilter'] ?? null,
            githubMcpToolOptions: $data['githubMcpToolOptions'] ?? null,
            githubMcpUserOverride: $data['githubMcpUserOverride'] ?? null,
            secretStore: $data['secretStore'] ?? null,
            activeGitHubToken: $data['activeGitHubToken'] ?? null,
            useCachedToolSnapshots: $data['useCachedToolSnapshots'] ?? null,
            forceRestart: $data['forceRestart'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mcpServers' => array_map(
                static fn (McpServerValue|array $server) => $server instanceof McpServerValue ? $server->toArray() : $server,
                $this->mcpServers,
            ),
            'disabledServers' => $this->disabledServers,
            'enabledServers' => $this->enabledServers,
            'cliEnabledServers' => $this->cliEnabledServers,
            'configuredDisabledServers' => $this->configuredDisabledServers,
            'mcp3pEnabled' => $this->mcp3pEnabled,
            'includeWorkspaceSources' => $this->includeWorkspaceSources,
            'configFilter' => $this->configFilter,
            'githubMcpToolOptions' => $this->githubMcpToolOptions,
            'githubMcpUserOverride' => $this->githubMcpUserOverride,
            'secretStore' => $this->secretStore,
            'activeGitHubToken' => $this->activeGitHubToken,
            'useCachedToolSnapshots' => $this->useCachedToolSnapshots,
            'forceRestart' => $this->forceRestart,
        ], static fn ($value) => $value !== null);
    }
}
