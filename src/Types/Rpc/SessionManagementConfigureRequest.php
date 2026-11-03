<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionManagementScope;

/**
 * Updates the session's live policy without changing persistent host settings.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementConfigureRequest implements Arrayable
{
    public function __construct(
        public ?string $cwd = null,
        public ?bool $enabled = null,
        public SessionManagementScope|string|null $scope = null,
        public SessionManagementToolOverrides|array|null $tools = null,
        public ?bool $hasCreateHandler = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $scope = $data['scope'] ?? null;
        if (is_string($scope)) {
            $scope = SessionManagementScope::tryFrom($scope) ?? $scope;
        }

        $tools = $data['tools'] ?? null;
        if (is_array($tools)) {
            $tools = SessionManagementToolOverrides::fromArray($tools);
        }

        return new self(
            cwd: $data['cwd'] ?? null,
            enabled: $data['enabled'] ?? null,
            scope: $scope,
            tools: $tools,
            hasCreateHandler: $data['hasCreateHandler'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cwd' => $this->cwd,
            'enabled' => $this->enabled,
            'scope' => $this->scope instanceof SessionManagementScope ? $this->scope->value : $this->scope,
            'tools' => $this->tools instanceof SessionManagementToolOverrides ? $this->tools->toArray() : $this->tools,
            'hasCreateHandler' => $this->hasCreateHandler,
        ], fn ($value): bool => $value !== null);
    }
}
