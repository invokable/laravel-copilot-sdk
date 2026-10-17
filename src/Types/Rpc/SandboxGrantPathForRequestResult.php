<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of accepting a sandbox path grant.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxGrantPathForRequestResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether this call resolved the pending request and added the path
     *                         to the session's sandbox policy.
     */
    public function __construct(
        public bool $success,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: Arr::boolean($data, 'success'),
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
        ];
    }
}
