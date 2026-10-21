<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\MemoryMode;

/**
 * Configuration for the memory feature, which lets the agent persist and recall
 * information across turns.
 */
readonly class MemoryConfiguration implements Arrayable
{
    /**
     * @param  bool  $enabled  Whether the memory feature is enabled for this session.
     * @param  MemoryMode|string|null  $mode  Memory injection mode. When omitted, the runtime default applies.
     */
    public function __construct(
        public bool $enabled,
        public MemoryMode|string|null $mode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $mode = null;
        if (isset($data['mode'])) {
            $mode = $data['mode'] instanceof MemoryMode
                ? $data['mode']
                : Arr::string($data, 'mode');

            if (is_string($mode)) {
                $mode = MemoryMode::tryFrom($mode) ?? $mode;
            }
        }

        return new self(
            enabled: Arr::boolean($data, 'enabled', false),
            mode: $mode,
        );
    }

    public function toArray(): array
    {
        $mode = $this->mode instanceof MemoryMode ? $this->mode->value : $this->mode;

        return array_filter([
            'enabled' => $this->enabled,
            'mode' => $mode,
        ], static fn ($value) => $value !== null);
    }
}
