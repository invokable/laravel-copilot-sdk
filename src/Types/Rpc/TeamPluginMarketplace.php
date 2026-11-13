<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A Team marketplace fallback.
 *
 * @experimental
 */
readonly class TeamPluginMarketplace implements Arrayable
{
    public function __construct(
        public TeamPluginMarketplaceSource $source,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            source: TeamPluginMarketplaceSource::fromArray($data['source']),
        );
    }

    public function toArray(): array
    {
        return [
            'source' => $this->source->toArray(),
        ];
    }
}
