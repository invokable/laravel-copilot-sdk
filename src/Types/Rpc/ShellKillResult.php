<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of a shell kill request.
 */
readonly class ShellKillResult implements Arrayable
{
    /**
     * @param  bool  $killed  Whether the signal was sent successfully
     */
    public function __construct(
        public bool $killed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            killed: is_int($data['killed'] ?? null) ? (bool) Arr::integer($data, 'killed') : Arr::boolean($data, 'killed'),
        );
    }

    public function toArray(): array
    {
        return [
            'killed' => $this->killed,
        ];
    }
}
