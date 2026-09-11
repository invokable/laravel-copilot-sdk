<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of attempting to disable sandboxing for the current session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxDisableForSessionResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether this call resolved the pending request and applied the session opt-out.
     * @param  bool  $enabled  The authoritative sandbox enabled state after the operation.
     */
    public function __construct(
        public bool $success,
        public bool $enabled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: Arr::boolean($data, 'success'),
            enabled: Arr::boolean($data, 'enabled'),
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'enabled' => $this->enabled,
        ];
    }
}
