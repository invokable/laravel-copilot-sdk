<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AccountKind;

readonly class AccountStatus implements Arrayable
{
    public function __construct(
        public string $host,
        public string $login,
        public AccountKind|string $kind,
        public bool $active,
        public string $selectionId,
        public ?string $derivedFrom = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self($data['host'] ?? '', $data['login'] ?? '', AccountKind::tryFrom($data['kind'] ?? '') ?? ($data['kind'] ?? ''), (bool) ($data['active'] ?? false), $data['selectionId'] ?? '', $data['derivedFrom'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter(['host' => $this->host, 'login' => $this->login, 'kind' => $this->kind instanceof AccountKind ? $this->kind->value : $this->kind, 'derivedFrom' => $this->derivedFrom, 'active' => $this->active, 'selectionId' => $this->selectionId], fn ($value) => $value !== null);
    }
}
