<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for setting session mode.
 */
readonly class SessionModeSetParams implements Arrayable
{
    /**
     * @param  string  $mode  The mode to switch to. Valid values: "interactive", "plan", "autopilot".
     */
    public function __construct(
        public string $mode,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mode: $data['mode'],
        );
    }

    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
        ];
    }
}
