<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Cursor-based request for retained lifecycle diagnostic events.
 *
 * @internal
 */
readonly class LifecycleDiagnosticsReadRequest implements Arrayable
{
    public function __construct(
        public ?string $cursor = null,
        public ?int $max = null,
        public ?int $waitMs = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cursor: isset($data['cursor']) ? Arr::string($data, 'cursor') : null,
            max: isset($data['max']) ? Arr::integer($data, 'max') : null,
            waitMs: isset($data['waitMs']) ? Arr::integer($data, 'waitMs') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cursor' => $this->cursor,
            'max' => $this->max,
            'waitMs' => $this->waitMs,
        ], fn ($value) => $value !== null);
    }
}
