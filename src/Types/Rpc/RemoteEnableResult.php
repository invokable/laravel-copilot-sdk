<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of enabling remote session support.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RemoteEnableResult implements Arrayable
{
    /**
     * @param  bool  $remoteSteerable  Whether remote steering is enabled
     * @param  ?string  $url  Mission Control frontend URL for this session
     */
    public function __construct(
        public bool $remoteSteerable,
        public ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            remoteSteerable: Arr::boolean($data, 'remoteSteerable', false),
            url: $data['url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'remoteSteerable' => $this->remoteSteerable,
            'url' => $this->url,
        ], fn ($v) => $v !== null);
    }
}
