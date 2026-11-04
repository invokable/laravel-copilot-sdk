<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class GitHubReleaseAbandonRequestResult implements Arrayable
{
    public function __construct(public bool $abandoned) {}

    public static function fromArray(array $data): self
    {
        return new self(abandoned: Arr::boolean($data, 'abandoned', false));
    }

    public function toArray(): array
    {
        return ['abandoned' => $this->abandoned];
    }
}
