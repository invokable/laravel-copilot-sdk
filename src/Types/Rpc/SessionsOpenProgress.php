<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Progress step returned by a session handoff.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsOpenProgress implements Arrayable
{
    public function __construct(
        public string $step,
        public string $status,
        public ?string $message = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            step: Arr::string($data, 'step'),
            status: Arr::string($data, 'status'),
            message: $data['message'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'step' => $this->step,
            'status' => $this->status,
            'message' => $this->message,
        ], fn ($value) => $value !== null);
    }
}
