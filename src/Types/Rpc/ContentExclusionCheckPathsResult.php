<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class ContentExclusionCheckPathsResult implements Arrayable
{
    /**
     * @param  array<int, ContentExclusionPathCheck>  $checks
     */
    public function __construct(
        public bool $available,
        public array $checks,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            available: Arr::boolean($data, 'available', false),
            checks: array_map(
                static fn (array $check): ContentExclusionPathCheck => ContentExclusionPathCheck::fromArray($check),
                $data['checks'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'available' => $this->available,
            'checks' => array_map(
                static fn (ContentExclusionPathCheck $check): array => $check->toArray(),
                $this->checks,
            ),
        ];
    }

    public function isExcluded(string $path): bool
    {
        if (! $this->available) {
            return true;
        }

        foreach ($this->checks as $check) {
            if ($check->path === $path) {
                return $check->excluded;
            }
        }

        return true;
    }
}
