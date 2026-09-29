<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** @experimental */
readonly class AuthValidationError implements Arrayable
{
    public function __construct(
        public string $message,
        public ?string $githubMessage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: (string) ($data['message'] ?? ''),
            githubMessage: isset($data['githubMessage']) ? (string) $data['githubMessage'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'message' => $this->message,
            'githubMessage' => $this->githubMessage,
        ], static fn ($value): bool => $value !== null);
    }
}
