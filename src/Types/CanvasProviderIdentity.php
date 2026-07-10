<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Stable identity for a host/SDK connection that supplies built-in canvases.
 *
 * When set on session create or resume, the runtime uses `id` verbatim
 * as the agent-facing canvas extension id, so canvases declared on a control
 * connection survive stdio reconnect and CLI process restart instead of being
 * re-keyed to a per-connection id.
 */
readonly class CanvasProviderIdentity implements Arrayable
{
    /**
     * @param  string  $id  Opaque, stable provider id used verbatim as the canvas extension id.
     * @param  string|null  $name  Optional display name surfaced as the canvas extension name.
     */
    public function __construct(
        public string $id,
        public ?string $name = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id'),
            name: isset($data['name']) ? Arr::string($data, 'name') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
        ], fn ($v) => $v !== null);
    }
}
