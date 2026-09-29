<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class AuthStatusDto implements Arrayable
{
    public function __construct(public bool $isAuthenticated, public int $accountCount, public ?string $activeHost = null, public ?string $activeLogin = null, public ?string $copilotPlan = null) {}

    public static function fromArray(array $data): self
    {
        return new self((bool) ($data['isAuthenticated'] ?? false), (int) ($data['accountCount'] ?? 0), $data['activeHost'] ?? null, $data['activeLogin'] ?? null, $data['copilotPlan'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter(['isAuthenticated' => $this->isAuthenticated, 'accountCount' => $this->accountCount, 'activeHost' => $this->activeHost, 'activeLogin' => $this->activeLogin, 'copilotPlan' => $this->copilotPlan], fn ($value) => $value !== null);
    }
}
