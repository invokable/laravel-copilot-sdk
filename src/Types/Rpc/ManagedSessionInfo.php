<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * An opted-in local session, reported by its live owner.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ManagedSessionInfo implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public string $name,
        public string $cwd,
        public string $state,
        public ?string $sessionStatePath = null,
        public ?string $model = null,
        public ?string $parentSessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['sessionId'],
            name: $data['name'],
            cwd: $data['cwd'],
            state: $data['state'],
            sessionStatePath: $data['sessionStatePath'] ?? null,
            model: $data['model'] ?? null,
            parentSessionId: $data['parentSessionId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
            'name' => $this->name,
            'cwd' => $this->cwd,
            'sessionStatePath' => $this->sessionStatePath,
            'state' => $this->state,
            'model' => $this->model,
            'parentSessionId' => $this->parentSessionId,
        ], fn ($value): bool => $value !== null);
    }
}
