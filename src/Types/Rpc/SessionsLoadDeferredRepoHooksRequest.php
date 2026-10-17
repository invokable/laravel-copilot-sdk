<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Request to load repository hooks deferred until folder trust. */
readonly class SessionsLoadDeferredRepoHooksRequest implements Arrayable
{
    public function __construct(
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
        );
    }

    public function toArray(): array
    {
        return ['sessionId' => $this->sessionId];
    }
}
