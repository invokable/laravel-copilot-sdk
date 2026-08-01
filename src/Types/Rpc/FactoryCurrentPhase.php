<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * The factory phase a run is currently executing.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryCurrentPhase implements Arrayable
{
    /**
     * @param  string  $id  Stable phase identifier.
     * @param  ?int  $ordinal  Zero-based declared phase ordinal, or null for an ad-hoc phase.
     */
    public function __construct(
        public string $id,
        public ?int $ordinal = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id', ''),
            ordinal: $data['ordinal'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ordinal' => $this->ordinal,
        ];
    }
}
