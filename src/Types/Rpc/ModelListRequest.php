<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional listing options for session model list.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ModelListRequest implements Arrayable
{
    /**
     * @param  ?bool  $skipCache  If true, bypasses the per-session model list cache and re-fetches from CAPI.
     */
    public function __construct(
        public ?bool $skipCache = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            skipCache: $data['skipCache'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'skipCache' => $this->skipCache,
        ], fn ($v) => $v !== null);
    }
}
