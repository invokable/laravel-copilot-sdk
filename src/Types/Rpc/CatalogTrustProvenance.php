<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\CatalogTrustSource;

/**
 * Where and when the runtime observed the trust metadata. Observation time is not the
 * authority's evaluation time and must not be used to infer staleness.
 *
 * @experimental
 */
readonly class CatalogTrustProvenance implements Arrayable
{
    /**
     * @param  string  $observedAt  ISO 8601 timestamp with a timezone offset at which the runtime observed the search result carrying this trust field.
     */
    public function __construct(
        public CatalogTrustSource $source,
        public string $observedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            source: CatalogTrustSource::from(Arr::string($data, 'source')),
            observedAt: Arr::string($data, 'observedAt'),
        );
    }

    public function toArray(): array
    {
        return [
            'source' => $this->source->value,
            'observedAt' => $this->observedAt,
        ];
    }
}
