<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class PermissionsExtensionApprovalsListParams implements Arrayable
{
    public function __construct(
        public ?string $sessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: isset($data['sessionId']) ? Arr::string($data, 'sessionId') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
        ], fn ($value): bool => $value !== null);
    }
}
