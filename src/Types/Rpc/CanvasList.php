<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Canvases available in the current session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CanvasList implements Arrayable
{
    /**
     * @param  DiscoveredCanvas[]  $canvases
     */
    public function __construct(
        public array $canvases = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            canvases: array_map(
                fn (DiscoveredCanvas|array $canvas) => $canvas instanceof DiscoveredCanvas
                    ? $canvas
                    : DiscoveredCanvas::fromArray($canvas),
                $data['canvases'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'canvases' => array_map(
                fn (DiscoveredCanvas|array $canvas) => $canvas instanceof DiscoveredCanvas
                    ? $canvas->toArray()
                    : DiscoveredCanvas::fromArray($canvas)->toArray(),
                $this->canvases,
            ),
        ];
    }
}
