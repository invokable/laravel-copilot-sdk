<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Report a session's remote-steerability change to the runtime.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RemoteNotifySteerableChangedRequest implements Arrayable
{
    public function __construct(
        public bool $remoteSteerable,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            remoteSteerable: Arr::boolean($data, 'remoteSteerable'),
        );
    }

    public function toArray(): array
    {
        return [
            'remoteSteerable' => $this->remoteSteerable,
        ];
    }
}
