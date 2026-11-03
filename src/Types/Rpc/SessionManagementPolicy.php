<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionManagementScope;

/**
 * Opt-in policy for local session management.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementPolicy implements Arrayable
{
    public function __construct(
        public bool $enabled,
        public SessionManagementScope|string $scope,
        public bool $list,
        public bool $create,
        public bool $message,
        public bool $close,
    ) {}

    public static function fromArray(array $data): self
    {
        $scope = $data['scope'];

        return new self(
            enabled: $data['enabled'],
            scope: is_string($scope) ? (SessionManagementScope::tryFrom($scope) ?? $scope) : $scope,
            list: $data['list'],
            create: $data['create'],
            message: $data['message'],
            close: $data['close'],
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'scope' => $this->scope instanceof SessionManagementScope ? $this->scope->value : $this->scope,
            'list' => $this->list,
            'create' => $this->create,
            'message' => $this->message,
            'close' => $this->close,
        ];
    }
}
