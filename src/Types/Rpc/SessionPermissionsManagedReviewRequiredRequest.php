<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Permission request to evaluate against the session's current managed policy.
 *
 * @experimental
 */
readonly class SessionPermissionsManagedReviewRequiredRequest implements Arrayable
{
    /**
     * @param  array<string, mixed>  $request  Original permission request payload.
     */
    public function __construct(
        public array $request,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            request: Arr::array($data, 'request'),
        );
    }

    public function toArray(): array
    {
        return [
            'request' => $this->request,
        ];
    }
}
