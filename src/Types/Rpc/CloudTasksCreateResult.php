<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The outcome of starting a cloud coding-agent task.
 *
 * @experimental
 */
readonly class CloudTasksCreateResult implements Arrayable
{
    public function __construct(
        public bool $ok,
        public ?string $responseJson = null,
        public ?string $reason = null,
        public ?string $message = null,
        public ?int $statusCode = null,
        public ?string $errorMessage = null,
        public ?string $errorPayloadJson = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            ok: $data['ok'],
            responseJson: $data['responseJson'] ?? null,
            reason: $data['reason'] ?? null,
            message: $data['message'] ?? null,
            statusCode: $data['statusCode'] ?? null,
            errorMessage: $data['errorMessage'] ?? null,
            errorPayloadJson: $data['errorPayloadJson'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ok' => $this->ok,
            'responseJson' => $this->responseJson,
            'reason' => $this->reason,
            'message' => $this->message,
            'statusCode' => $this->statusCode,
            'errorMessage' => $this->errorMessage,
            'errorPayloadJson' => $this->errorPayloadJson,
        ], fn ($value): bool => $value !== null);
    }
}
