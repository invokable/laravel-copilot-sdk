<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class McpSecretsSaveResult implements Arrayable
{
    public function __construct(
        public bool $usedKeychain,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            usedKeychain: Arr::boolean($data, 'usedKeychain'),
        );
    }

    public function toArray(): array
    {
        return ['usedKeychain' => $this->usedKeychain];
    }
}
