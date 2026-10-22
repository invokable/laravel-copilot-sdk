<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Empty acknowledgement for an updated host session creation policy. */
readonly class HostSetSessionCreationPolicyResult implements Arrayable
{
    public static function fromArray(array $data): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return [];
    }
}
