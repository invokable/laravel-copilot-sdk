<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AuthInfoType;

/**
 * Session authentication status.
 */
readonly class SessionAuthStatus implements Arrayable
{
    /**
     * @param  bool  $isAuthenticated  Whether the session has resolved authentication.
     * @param  AuthInfoType|string|null  $authType  Authentication type.
     * @param  ?string  $copilotPlan  Copilot plan tier (e.g., individual_pro, business).
     * @param  ?string  $host  Authentication host URL.
     * @param  ?string  $login  Authenticated login/username, if available.
     * @param  ?string  $statusMessage  Human-readable authentication status description.
     */
    public function __construct(
        public bool $isAuthenticated,
        public AuthInfoType|string|null $authType = null,
        public ?string $copilotPlan = null,
        public ?string $host = null,
        public ?string $login = null,
        public ?string $statusMessage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $authType = isset($data['authType'])
            ? (AuthInfoType::tryFrom($data['authType']) ?? $data['authType'])
            : null;

        return new self(
            isAuthenticated: $data['isAuthenticated'],
            authType: $authType,
            copilotPlan: $data['copilotPlan'] ?? null,
            host: $data['host'] ?? null,
            login: $data['login'] ?? null,
            statusMessage: $data['statusMessage'] ?? null,
        );
    }

    public function toArray(): array
    {
        $authType = $this->authType instanceof AuthInfoType
            ? $this->authType->value
            : $this->authType;

        return array_filter([
            'isAuthenticated' => $this->isAuthenticated,
            'authType' => $authType,
            'copilotPlan' => $this->copilotPlan,
            'host' => $this->host,
            'login' => $this->login,
            'statusMessage' => $this->statusMessage,
        ], fn ($v) => $v !== null);
    }
}
