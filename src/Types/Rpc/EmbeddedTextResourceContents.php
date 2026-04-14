<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Text content of an embedded resource.
 */
readonly class EmbeddedTextResourceContents implements Arrayable
{
    /**
     * @param  string  $uri  URI identifying the resource
     * @param  string  $text  Text content of the resource
     * @param  ?string  $mimeType  MIME type of the text content
     */
    public function __construct(
        public string $uri,
        public string $text,
        public ?string $mimeType = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uri: $data['uri'],
            text: $data['text'],
            mimeType: $data['mimeType'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'uri' => $this->uri,
            'text' => $this->text,
            'mimeType' => $this->mimeType,
        ], fn ($v) => $v !== null);
    }
}
