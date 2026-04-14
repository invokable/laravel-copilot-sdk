<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Base64-encoded binary content of an embedded resource.
 */
readonly class EmbeddedBlobResourceContents implements Arrayable
{
    /**
     * @param  string  $uri  URI identifying the resource
     * @param  string  $blob  Base64-encoded binary content of the resource
     * @param  ?string  $mimeType  MIME type of the blob content
     */
    public function __construct(
        public string $uri,
        public string $blob,
        public ?string $mimeType = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uri: $data['uri'],
            blob: $data['blob'],
            mimeType: $data['mimeType'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'uri' => $this->uri,
            'blob' => $this->blob,
            'mimeType' => $this->mimeType,
        ], fn ($v) => $v !== null);
    }
}
