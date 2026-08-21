<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of moving in-flight MCP loading to the background.
 *
 * @experimental
 */
readonly class MoveMcpLoadingToBackgroundResult implements Arrayable
{
    /**
     * @param  bool  $movedToBackground  Whether an in-flight MCP load was moved to the background,
     *                                   releasing turns that were waiting on it. False when no MCP
     *                                   load was in flight or the waiting turns had already been released.
     */
    public function __construct(
        public bool $movedToBackground,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            movedToBackground: Arr::boolean($data, 'movedToBackground'),
        );
    }

    public function toArray(): array
    {
        return [
            'movedToBackground' => $this->movedToBackground,
        ];
    }
}
