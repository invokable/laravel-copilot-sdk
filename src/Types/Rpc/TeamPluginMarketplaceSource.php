<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A GitHub repository source for a Team marketplace.
 *
 * @experimental
 */
readonly class TeamPluginMarketplaceSource implements Arrayable
{
    public function __construct(
        public string $source,
        public string $repo,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            source: $data['source'],
            repo: $data['repo'],
        );
    }

    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'repo' => $this->repo,
        ];
    }
}
