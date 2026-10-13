<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Indicates whether an auto-generated summary was applied as the session name.
 *
 * @experimental
 */
readonly class NameSetAutoResult implements Arrayable
{
    /**
     * @param  bool  $applied  Whether the summary was persisted as the session name.
     */
    public function __construct(
        public bool $applied,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            applied: $data['applied'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'applied' => $this->applied,
        ];
    }
}
