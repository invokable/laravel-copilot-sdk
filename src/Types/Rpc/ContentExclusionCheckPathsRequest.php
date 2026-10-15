<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** @experimental */
readonly class ContentExclusionCheckPathsRequest implements Arrayable
{
    /**
     * @param  array<int, string>  $paths
     */
    public function __construct(
        public array $paths,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            paths: $data['paths'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'paths' => $this->paths,
        ];
    }
}
