<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional flags controlling which side effects a plugin reload performs.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class PluginsReloadRequest implements Arrayable
{
    /**
     * @param  ?bool  $deferRepoHooks  When true, skip repo-level hooks during the hook reload.
     * @param  ?bool  $reloadCustomAgents  Re-run custom-agent discovery after refreshing plugins. Defaults to true.
     * @param  ?bool  $reloadExtensions  Re-discover and relaunch subprocess extensions after refreshing plugins. Defaults to true.
     * @param  ?bool  $reloadHooks  Re-load user, plugin, and repo hooks. Defaults to true.
     * @param  ?bool  $reloadMcp  Reload MCP server connections after refreshing plugins. Defaults to true.
     */
    public function __construct(
        public ?bool $deferRepoHooks = null,
        public ?bool $reloadCustomAgents = null,
        public ?bool $reloadExtensions = null,
        public ?bool $reloadHooks = null,
        public ?bool $reloadMcp = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            deferRepoHooks: $data['deferRepoHooks'] ?? null,
            reloadCustomAgents: $data['reloadCustomAgents'] ?? null,
            reloadExtensions: $data['reloadExtensions'] ?? null,
            reloadHooks: $data['reloadHooks'] ?? null,
            reloadMcp: $data['reloadMcp'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'deferRepoHooks' => $this->deferRepoHooks,
            'reloadCustomAgents' => $this->reloadCustomAgents,
            'reloadExtensions' => $this->reloadExtensions,
            'reloadHooks' => $this->reloadHooks,
            'reloadMcp' => $this->reloadMcp,
        ], fn ($v) => $v !== null);
    }
}
