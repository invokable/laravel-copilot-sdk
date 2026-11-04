<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The HTTP outcome of reading a cloud coding-agent task.
 *
 * @experimental
 */
readonly class CloudTasksGetResult implements Arrayable
{
    public function __construct(
        public bool $ok,
        public ?string $payloadJson = null,
        public ?int $statusCode = null,
        public ?string $errorMessage = null,
        public ?string $errorKind = null,
        public ?string $errorPayloadJson = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            ok: $data['ok'],
            payloadJson: $data['payloadJson'] ?? null,
            statusCode: $data['statusCode'] ?? null,
            errorMessage: $data['errorMessage'] ?? null,
            errorKind: $data['errorKind'] ?? null,
            errorPayloadJson: $data['errorPayloadJson'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ok' => $this->ok,
            'payloadJson' => $this->payloadJson,
            'statusCode' => $this->statusCode,
            'errorMessage' => $this->errorMessage,
            'errorKind' => $this->errorKind,
            'errorPayloadJson' => $this->errorPayloadJson,
        ], fn ($value): bool => $value !== null);
    }
}
