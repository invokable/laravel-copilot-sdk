<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of an ephemeral UI query.
 */
readonly class UIEphemeralQueryResult implements Arrayable
{
    public function __construct(
        public string $answer,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            answer: Arr::string($data, 'answer'),
        );
    }

    public function toArray(): array
    {
        return [
            'answer' => $this->answer,
        ];
    }
}
