<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Vision-specific limit overrides.
 */
readonly class ModelCapabilitiesOverrideLimitsVision implements Arrayable
{
    /**
     * @param  ?array  $supported_media_types  MIME types the model accepts
     * @param  ?int  $max_prompt_images  Maximum number of images per prompt
     * @param  ?int  $max_prompt_image_size  Maximum image size in bytes
     */
    public function __construct(
        public ?array $supported_media_types = null,
        public ?int $max_prompt_images = null,
        public ?int $max_prompt_image_size = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            supported_media_types: $data['supported_media_types'] ?? null,
            max_prompt_images: $data['max_prompt_images'] ?? null,
            max_prompt_image_size: $data['max_prompt_image_size'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'supported_media_types' => $this->supported_media_types,
            'max_prompt_images' => $this->max_prompt_images,
            'max_prompt_image_size' => $this->max_prompt_image_size,
        ], fn ($v) => $v !== null);
    }
}
