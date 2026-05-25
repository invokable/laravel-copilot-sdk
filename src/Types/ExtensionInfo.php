<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Stable extension identity for session participants that provide canvases.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class ExtensionInfo implements Arrayable
{
    /**
     * @param  string  $source  Extension namespace/source, e.g. "github-app".
     * @param  string  $name  Stable provider name within the source namespace.
     */
    public function __construct(
        public string $source,
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            source: $data['source'],
            name: $data['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'name' => $this->name,
        ];
    }
}
