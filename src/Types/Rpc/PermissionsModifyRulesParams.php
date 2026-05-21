<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\PermissionsModifyRulesScope;

/**
 * Scope and add/remove instructions for modifying permission rules.
 */
readonly class PermissionsModifyRulesParams implements Arrayable
{
    public function __construct(
        public PermissionsModifyRulesScope|string $scope,
        public ?array $add = null,
        public ?array $remove = null,
        public ?bool $removeAll = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $scope = $data['scope'] ?? PermissionsModifyRulesScope::SESSION->value;
        if (is_string($scope)) {
            $scope = PermissionsModifyRulesScope::from($scope);
        }

        return new self(
            scope: $scope,
            add: $data['add'] ?? null,
            remove: $data['remove'] ?? null,
            removeAll: $data['removeAll'] ?? null,
        );
    }

    public function toArray(): array
    {
        $scope = $this->scope instanceof PermissionsModifyRulesScope
            ? $this->scope->value
            : $this->scope;

        return array_filter([
            'scope' => $scope,
            'add' => $this->add,
            'remove' => $this->remove,
            'removeAll' => $this->removeAll,
        ], fn ($value): bool => $value !== null);
    }
}
