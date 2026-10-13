<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Open canvas instances in the current session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CanvasListOpenResult implements Arrayable
{
    /**
     * @param  OpenCanvasInstance[]  $openCanvases
     */
    public function __construct(
        public array $openCanvases = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            openCanvases: array_map(
                fn (OpenCanvasInstance|array $canvas) => $canvas instanceof OpenCanvasInstance
                    ? $canvas
                    : OpenCanvasInstance::fromArray($canvas),
                $data['openCanvases'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'openCanvases' => array_map(
                fn (OpenCanvasInstance|array $canvas) => $canvas instanceof OpenCanvasInstance
                    ? $canvas->toArray()
                    : OpenCanvasInstance::fromArray($canvas)->toArray(),
                $this->openCanvases,
            ),
        ];
    }
}
