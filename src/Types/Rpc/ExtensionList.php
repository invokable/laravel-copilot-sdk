<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of listing extensions.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ExtensionList implements Arrayable
{
    /**
     * @param  array<ExtensionInfo>  $extensions  Discovered extensions and their current status
     */
    public function __construct(
        public array $extensions,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            extensions: array_map(
                fn (array $extension) => ExtensionInfo::fromArray($extension),
                $data['extensions'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'extensions' => array_map(fn (ExtensionInfo $ext) => $ext->toArray(), $this->extensions),
        ];
    }
}
