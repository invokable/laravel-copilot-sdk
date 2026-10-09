<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Non-persisted opt-in to image generation.
 *
 * Re-supply after runtime restart. BYOK image generation is not supported.
 *
 * @experimental This API may change or be removed in a future release.
 */
readonly class ImageGenerationConfig implements Arrayable
{
    /**
     * @param  ?bool  $enabled  Opt in to image generation through an authorized Copilot image model. False disables it.
     */
    public function __construct(
        public ?bool $enabled = null,
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            enabled: isset($data['enabled']) ? (bool) $data['enabled'] : null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
        ], fn ($v) => $v !== null);
    }
}
