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
     * @param  ?bool  $watermark  Request a visible "Made with AI" label. When omitted, the user's watermark
     *                            preference applies (default false). Re-supply after a cold resume. This does
     *                            not enable image generation or enforce enterprise policy.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?bool $watermark = null,
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            enabled: isset($data['enabled']) ? (bool) $data['enabled'] : null,
            watermark: isset($data['watermark']) ? (bool) $data['watermark'] : null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'watermark' => $this->watermark,
        ], fn ($v) => $v !== null);
    }
}
