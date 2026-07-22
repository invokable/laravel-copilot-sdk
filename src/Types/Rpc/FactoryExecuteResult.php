<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result returned by an extension factory closure.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryExecuteResult implements Arrayable
{
    /**
     * @param  mixed  $result  Factory result value.
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
        return [
            'result' => $this->result,
        ];
    }
}
