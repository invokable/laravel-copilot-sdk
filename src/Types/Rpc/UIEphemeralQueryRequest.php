<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for an ephemeral UI query.
 */
readonly class UIEphemeralQueryRequest implements Arrayable
{
    public function __construct(
        public string $question,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            question: Arr::string($data, 'question'),
        );
    }

    public function toArray(): array
    {
        return [
            'question' => $this->question,
        ];
    }
}
