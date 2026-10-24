<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Current activity flags for a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionActivity implements Arrayable
{
    public function __construct(
        public bool $abortable,
        public bool $hasActiveWork,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            abortable: Arr::boolean($data, 'abortable', false),
            hasActiveWork: Arr::boolean($data, 'hasActiveWork', false),
        );
    }

    public function toArray(): array
    {
        return [
            'abortable' => $this->abortable,
            'hasActiveWork' => $this->hasActiveWork,
        ];
    }
}
