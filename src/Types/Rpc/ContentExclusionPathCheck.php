<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class ContentExclusionPathCheck implements Arrayable
{
    public function __construct(
        public string $path,
        public bool $excluded,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: Arr::string($data, 'path'),
            excluded: Arr::boolean($data, 'excluded', false),
        );
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'excluded' => $this->excluded,
        ];
    }
}
