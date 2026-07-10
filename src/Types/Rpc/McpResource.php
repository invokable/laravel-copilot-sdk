<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * An MCP resource descriptor.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpResource implements Arrayable
{
    /**
     * @param  string  $uri  The resource URI (e.g. ui://... or file:///...)
     * @param  string  $name  The programmatic name of the resource
     * @param  string|null  $title  Optional human-readable display title
     * @param  string|null  $description  Optional description of what this resource represents
     * @param  string|null  $mimeType  MIME type of the resource, if known
     * @param  int|null  $size  Resource size in bytes, when known
     * @param  array|null  $icons  Icons associated with this resource
     * @param  array|null  $annotations  Resource annotations
     * @param  array|null  $_meta  Resource-level metadata
     * @param  array|null  $additionalProperties  Server-provided non-standard descriptor fields
     */
    public function __construct(
        public string $uri,
        public string $name,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $mimeType = null,
        public ?int $size = null,
        public ?array $icons = null,
        public ?array $annotations = null,
        public ?array $_meta = null,
        public ?array $additionalProperties = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uri: Arr::string($data, 'uri'),
            name: Arr::string($data, 'name'),
            title: isset($data['title']) ? Arr::string($data, 'title') : null,
            description: isset($data['description']) ? Arr::string($data, 'description') : null,
            mimeType: isset($data['mimeType']) ? Arr::string($data, 'mimeType') : null,
            size: isset($data['size']) ? Arr::integer($data, 'size') : null,
            icons: $data['icons'] ?? null,
            annotations: $data['annotations'] ?? null,
            _meta: $data['_meta'] ?? null,
            additionalProperties: $data['additionalProperties'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'uri' => $this->uri,
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'mimeType' => $this->mimeType,
            'size' => $this->size,
            'icons' => $this->icons,
            'annotations' => $this->annotations,
            '_meta' => $this->_meta,
            'additionalProperties' => $this->additionalProperties,
        ], fn ($v) => $v !== null);
    }
}
