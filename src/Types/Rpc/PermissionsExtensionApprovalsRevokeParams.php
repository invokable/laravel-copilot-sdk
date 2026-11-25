<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class PermissionsExtensionApprovalsRevokeParams implements Arrayable
{
    public function __construct(
        public string $extensionName,
        public string $installationKey,
        public ?string $sessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            extensionName: Arr::string($data, 'extensionName'),
            installationKey: Arr::string($data, 'installationKey'),
            sessionId: isset($data['sessionId']) ? Arr::string($data, 'sessionId') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'extensionName' => $this->extensionName,
            'installationKey' => $this->installationKey,
            'sessionId' => $this->sessionId,
        ], fn ($value): bool => $value !== null);
    }
}
