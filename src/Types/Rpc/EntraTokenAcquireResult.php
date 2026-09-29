<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** @experimental */
readonly class EntraTokenAcquireResult implements Arrayable
{
    public function __construct(
        public string $status,
        public ?string $accessToken = null,
        public ?int $expiresOnTimestamp = null,
        public ?string $accountId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: (string) ($data['status'] ?? ''),
            accessToken: isset($data['accessToken']) ? (string) $data['accessToken'] : null,
            expiresOnTimestamp: isset($data['expiresOnTimestamp']) ? (int) $data['expiresOnTimestamp'] : null,
            accountId: isset($data['accountId']) ? (string) $data['accountId'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'accessToken' => $this->accessToken,
            'expiresOnTimestamp' => $this->expiresOnTimestamp,
            'accountId' => $this->accountId,
        ], static fn ($value): bool => $value !== null);
    }
}
