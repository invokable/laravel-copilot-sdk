<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\RemoteSessionMode;

/**
 * Optional remote session mode ("off", "export", or "on"); defaults to enabling both export
 * and remote steering.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RemoteEnableRequest implements Arrayable
{
    /**
     * @param  RemoteSessionMode|string|null  $mode  Per-session remote mode.
     */
    public function __construct(
        public RemoteSessionMode|string|null $mode = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $mode = null;
        if (isset($data['mode'])) {
            $mode = $data['mode'] instanceof RemoteSessionMode
                ? $data['mode']
                : RemoteSessionMode::from($data['mode']);
        }

        return new static(mode: $mode);
    }

    public function toArray(): array
    {
        $mode = $this->mode instanceof RemoteSessionMode
            ? $this->mode->value
            : $this->mode;

        return array_filter([
            'mode' => $mode,
        ], fn ($value) => $value !== null);
    }
}
