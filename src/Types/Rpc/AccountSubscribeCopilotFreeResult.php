<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Outcome of a Copilot Free enrollment attempt.
 */
readonly class AccountSubscribeCopilotFreeResult implements Arrayable
{
    /**
     * @param  string  $kind  Enrollment outcome, such as subscribed, no-token, or failed.
     * @param  string|null  $status  GitHub's enrollment status when subscribed.
     * @param  string|null  $message  Explanation to show the user.
     */
    public function __construct(
        public string $kind,
        public ?string $status = null,
        public ?string $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: Arr::string($data, 'kind'),
            status: $data['status'] ?? null,
            message: $data['message'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind,
            'status' => $this->status,
            'message' => $this->message,
        ], fn ($value) => $value !== null);
    }
}
