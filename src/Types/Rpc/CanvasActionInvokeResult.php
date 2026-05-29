<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Provider-supplied canvas action result.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CanvasActionInvokeResult implements Arrayable
{
    public function __construct(
        public array $data = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(data: $data);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
