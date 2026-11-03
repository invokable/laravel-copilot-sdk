<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ContextTier;
use Revolution\Copilot\Enums\PermissionMode;

/**
 * Host callback request to create and supervise a separate session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementCreateHostRequest implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public string $parentSessionId,
        public string $newSessionId,
        public string $cwd,
        public string $configDir,
        public PermissionMode|string $permissionMode,
        public ?string $model = null,
        public ?string $providerId = null,
        public ?string $reasoningEffort = null,
        public ContextTier|string|null $contextTier = null,
        public ?array $allowedModels = null,
        public ?string $name = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $permissionMode = $data['permissionMode'];
        if (is_string($permissionMode)) {
            $permissionMode = PermissionMode::tryFrom($permissionMode) ?? $permissionMode;
        }

        $contextTier = $data['contextTier'] ?? null;
        if (is_string($contextTier)) {
            $contextTier = ContextTier::tryFrom($contextTier) ?? $contextTier;
        }

        return new self(
            sessionId: $data['sessionId'],
            parentSessionId: $data['parentSessionId'],
            newSessionId: $data['newSessionId'],
            cwd: $data['cwd'],
            configDir: $data['configDir'],
            permissionMode: $permissionMode,
            model: $data['model'] ?? null,
            providerId: $data['providerId'] ?? null,
            reasoningEffort: $data['reasoningEffort'] ?? null,
            contextTier: $contextTier,
            allowedModels: $data['allowedModels'] ?? null,
            name: $data['name'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
            'parentSessionId' => $this->parentSessionId,
            'newSessionId' => $this->newSessionId,
            'cwd' => $this->cwd,
            'configDir' => $this->configDir,
            'permissionMode' => $this->permissionMode instanceof PermissionMode ? $this->permissionMode->value : $this->permissionMode,
            'model' => $this->model,
            'providerId' => $this->providerId,
            'reasoningEffort' => $this->reasoningEffort,
            'contextTier' => $this->contextTier instanceof ContextTier ? $this->contextTier->value : $this->contextTier,
            'allowedModels' => $this->allowedModels,
            'name' => $this->name,
        ], fn ($value): bool => $value !== null);
    }
}
