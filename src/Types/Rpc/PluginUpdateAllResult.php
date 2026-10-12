<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of updating all installed plugins.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginUpdateAllResult implements Arrayable
{
    /**
     * @param  array<PluginUpdateAllEntry>  $results
     */
    public function __construct(
        public array $results,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            results: array_map(
                fn (array $result) => PluginUpdateAllEntry::fromArray($result),
                $data['results'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'results' => array_map(
                fn (PluginUpdateAllEntry $result) => $result->toArray(),
                $this->results,
            ),
        ];
    }
}
