<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Canvas action invocation result.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class CanvasInvokeActionResult implements Arrayable
{
    /**
     * @param  mixed  $result  Provider-supplied action result.
     */
    public function __construct(
        public mixed $result = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            result: $data['result'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'result' => $this->result,
        ], fn ($value) => $value !== null);
    }
}
