<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * User input response for a pending UI request.
 */
readonly class UIUserInputResponse implements Arrayable
{
    public function __construct(
        public string $answer,
        public bool $wasFreeform,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            answer: Arr::string($data, 'answer'),
            wasFreeform: Arr::boolean($data, 'wasFreeform'),
        );
    }

    public function toArray(): array
    {
        return [
            'answer' => $this->answer,
            'wasFreeform' => $this->wasFreeform,
        ];
    }
}
