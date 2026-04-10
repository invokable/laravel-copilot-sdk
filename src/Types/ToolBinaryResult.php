<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Binary result item for tool responses (images, resources, etc.).
 */
readonly class ToolBinaryResult implements Arrayable
{
    /**
     * @param  string  $data  Base64-encoded binary data
     * @param  string  $mimeType  MIME type of the data
     * @param  string  $type  Content type: "image" or "resource"
     * @param  ?string  $description  Optional description (e.g. resource URI)
     */
    public function __construct(
        public string $data,
        public string $mimeType,
        public string $type = 'image',
        public ?string $description = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            data: $data['data'] ?? '',
            mimeType: $data['mimeType'] ?? '',
            type: $data['type'] ?? 'image',
            description: $data['description'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'data' => $this->data,
            'mimeType' => $this->mimeType,
            'type' => $this->type,
            'description' => $this->description,
        ], fn ($value) => $value !== null);
    }
}
