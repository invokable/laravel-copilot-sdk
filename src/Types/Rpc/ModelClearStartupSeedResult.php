<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Whether the expected startup model/provider seed was cleared. */
readonly class ModelClearStartupSeedResult implements Arrayable
{
    public function __construct(
        public bool $cleared,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(cleared: (bool) ($data['cleared'] ?? false));
    }

    public function toArray(): array
    {
        return ['cleared' => $this->cleared];
    }
}
