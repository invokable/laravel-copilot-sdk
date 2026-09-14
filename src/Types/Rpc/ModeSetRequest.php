<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for setting session mode.
 */
readonly class ModeSetRequest implements Arrayable
{
    /**
     * @param  string  $mode  The mode to switch to. Valid values: "interactive", "plan", "autopilot".
     * @param  ?string  $expectedMode  Mode the session must currently be in for the change to apply. When set and the session is in a different mode the request is a no-op and reports status 'unchanged'.
     */
    public function __construct(
        public string $mode,
        public ?string $expectedMode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mode: Arr::string($data, 'mode'),
            expectedMode: $data['expectedMode'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mode' => $this->mode,
            'expectedMode' => $this->expectedMode,
        ], fn ($v) => $v !== null);
    }
}
