<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Icon descriptor associated with an MCP prompt. */
readonly class McpPromptIcon implements Arrayable
{
    /** @param array<string> $sizes */
    public function __construct(
        public string $src,
        public ?string $mimeType = null,
        public ?array $sizes = null,
        public ?string $theme = null,
        public ?array $additionalProperties = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            src: Arr::string($data, 'src', ''),
            mimeType: $data['mimeType'] ?? null,
            sizes: $data['sizes'] ?? null,
            theme: $data['theme'] ?? null,
            additionalProperties: $data['additionalProperties'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'src' => $this->src,
            'mimeType' => $this->mimeType,
            'sizes' => $this->sizes,
            'theme' => $this->theme,
            'additionalProperties' => $this->additionalProperties,
        ], fn ($value) => $value !== null);
    }
}
