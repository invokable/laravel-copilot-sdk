<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional inputs for server-scoped hook discovery.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HooksDiscoverRequest implements Arrayable
{
    /**
     * @param  ?array<string>  $hookDirectories  Explicit file-hook roots to discover.
     * @param  ?array<string>  $projectPaths  Project directories to include in discovery.
     * @param  ?bool  $excludeHostHooks  Whether to omit host-owned user and plugin hooks.
     */
    public function __construct(
        public ?array $hookDirectories = null,
        public ?array $projectPaths = null,
        public ?bool $excludeHostHooks = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hookDirectories: $data['hookDirectories'] ?? null,
            projectPaths: $data['projectPaths'] ?? null,
            excludeHostHooks: $data['excludeHostHooks'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'hookDirectories' => $this->hookDirectories,
            'projectPaths' => $this->projectPaths,
            'excludeHostHooks' => $this->excludeHostHooks,
        ], fn ($value): bool => $value !== null);
    }
}
