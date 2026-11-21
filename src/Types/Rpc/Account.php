<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AccountKind;

readonly class Account implements Arrayable
{
    public function __construct(
        public string $host,
        public string $login,
        public AccountKind|string $kind,
        public bool $active,
        public ?string $selectionId = null,
        public ?string $derivedFrom = null,
        public ?string $authSource = null,
        public ?string $accountId = null,
        public ?string $label = null,
        public ?bool $switchable = null,
        public ?string $health = null,
        public ?string $statusMessage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            host: $data['host'] ?? '',
            login: $data['login'] ?? '',
            kind: AccountKind::tryFrom($data['kind'] ?? '') ?? ($data['kind'] ?? ''),
            active: (bool) ($data['active'] ?? false),
            selectionId: $data['selectionId'] ?? null,
            derivedFrom: $data['derivedFrom'] ?? null,
            authSource: $data['authSource'] ?? null,
            accountId: $data['accountId'] ?? null,
            label: $data['label'] ?? null,
            switchable: $data['switchable'] ?? null,
            health: $data['health'] ?? null,
            statusMessage: $data['statusMessage'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'host' => $this->host,
            'login' => $this->login,
            'kind' => $this->kind instanceof AccountKind ? $this->kind->value : $this->kind,
            'derivedFrom' => $this->derivedFrom,
            'active' => $this->active,
            'selectionId' => $this->selectionId,
            'authSource' => $this->authSource,
            'accountId' => $this->accountId,
            'label' => $this->label,
            'switchable' => $this->switchable,
            'health' => $this->health,
            'statusMessage' => $this->statusMessage,
        ], fn ($value) => $value !== null);
    }
}
