<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Marketplace source and optional working directory for relative-path resolution.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class PluginsMarketplacesAddRequest implements Arrayable
{
    /**
     * @param  string  $source  Marketplace source. Accepts the same forms as the CLI: "owner/repo" or "owner/repo#ref" (GitHub), an http/https/ssh URL (optionally with #ref), a git scp-style URL (user@host:path), or a local path.
     * @param  string|null  $workingDirectory  Working directory used to resolve relative local paths in `source`. Defaults to the server's current working directory.
     */
    public function __construct(
        public string $source,
        public ?string $workingDirectory = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            source: Arr::string($data, 'source'),
            workingDirectory: $data['workingDirectory'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'source' => $this->source,
            'workingDirectory' => $this->workingDirectory,
        ], fn ($value) => $value !== null);
    }
}
