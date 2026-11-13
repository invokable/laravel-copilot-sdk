<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class FetchReadResult implements Arrayable
{
    public function __construct(
        public bool $done,
        public string $body,
        public ?bool $endedAtUnverifiedEof = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            done: Arr::boolean($data, 'done', false),
            body: Arr::string($data, 'body', ''),
            endedAtUnverifiedEof: isset($data['endedAtUnverifiedEof'])
                ? Arr::boolean($data, 'endedAtUnverifiedEof')
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'done' => $this->done,
            'body' => $this->body,
            'endedAtUnverifiedEof' => $this->endedAtUnverifiedEof,
        ], static fn ($value): bool => $value !== null);
    }
}
