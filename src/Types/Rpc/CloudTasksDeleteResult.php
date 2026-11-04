<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The HTTP outcome of deleting a cloud coding-agent task.
 *
 * @experimental
 */
readonly class CloudTasksDeleteResult implements Arrayable
{
    public function __construct(
        public bool $ok,
        public ?int $statusCode = null,
        public ?string $errorMessage = null,
        public ?string $errorPayloadJson = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            ok: $data['ok'],
            statusCode: $data['statusCode'] ?? null,
            errorMessage: $data['errorMessage'] ?? null,
            errorPayloadJson: $data['errorPayloadJson'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ok' => $this->ok,
            'statusCode' => $this->statusCode,
            'errorMessage' => $this->errorMessage,
            'errorPayloadJson' => $this->errorPayloadJson,
        ], fn ($value): bool => $value !== null);
    }
}
