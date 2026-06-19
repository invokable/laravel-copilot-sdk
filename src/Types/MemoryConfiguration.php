<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Configuration for the memory feature, which lets the agent persist and recall
 * information across turns.
 */
readonly class MemoryConfiguration implements Arrayable
{
    /**
     * @param  bool  $enabled  Whether the memory feature is enabled for this session.
     */
    public function __construct(
        public bool $enabled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enabled: (bool) ($data['enabled'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
        ];
    }
}
