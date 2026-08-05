<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for clearing the conversation and seeding a fresh context window.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryClearContextRequest implements Arrayable
{
    public function __construct(
        public string $prompt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(prompt: Arr::string($data, 'prompt'));
    }

    public function toArray(): array
    {
        return ['prompt' => $this->prompt];
    }
}
