<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * MCP resource content with URI, optional MIME type, text or base64 blob, and resource metadata.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpResourceContent implements Arrayable
{
    /**
     * @param  string  $uri  The resource URI
     * @param  string|null  $mimeType  MIME type of the content
     * @param  string|null  $text  Text content (e.g. HTML)
     * @param  string|null  $blob  Base64-encoded binary content
     * @param  array|null  $_meta  Resource-level metadata (CSP, permissions, etc.)
     */
    public function __construct(
        public string $uri,
        public ?string $mimeType = null,
        public ?string $text = null,
        public ?string $blob = null,
        public ?array $_meta = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uri: Arr::string($data, 'uri'),
            mimeType: isset($data['mimeType']) ? Arr::string($data, 'mimeType') : null,
            text: isset($data['text']) ? Arr::string($data, 'text') : null,
            blob: isset($data['blob']) ? Arr::string($data, 'blob') : null,
            _meta: $data['_meta'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'uri' => $this->uri,
            'mimeType' => $this->mimeType,
            'text' => $this->text,
            'blob' => $this->blob,
            '_meta' => $this->_meta,
        ], fn ($v) => $v !== null);
    }
}
