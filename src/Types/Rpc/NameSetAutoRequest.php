<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for applying an auto-generated summary as the session name.
 *
 * @experimental
 */
readonly class NameSetAutoRequest implements Arrayable
{
    /**
     * @param  string  $summary  Auto-generated summary; whitespace-only values are ignored.
     */
    public function __construct(
        public string $summary,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            summary: Arr::string($data, 'summary'),
        );
    }

    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
        ];
    }
}
