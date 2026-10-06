<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for computing a workspace diff.
 *
 * @experimental
 */
readonly class WorkspacesDiffRequest implements Arrayable
{
    public function __construct(
        public string $mode,
        public ?bool $ignoreWhitespace = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mode: Arr::string($data, 'mode'),
            ignoreWhitespace: $data['ignoreWhitespace'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mode' => $this->mode,
            'ignoreWhitespace' => $this->ignoreWhitespace,
        ], static fn ($value) => $value !== null);
    }
}
