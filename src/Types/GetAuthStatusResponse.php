<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Response from auth.getStatus.
 */
readonly class GetAuthStatusResponse implements Arrayable
{
    /**
     * @param  bool  $isAuthenticated  Whether the user is authenticated
     * @param  ?string  $authType  Authentication type
     * @param  ?string  $host  GitHub host URL
     * @param  ?string  $login  User login name
     * @param  ?string  $statusMessage  Human-readable status message
     */
    public function __construct(
        public bool $isAuthenticated,
        public ?string $authType = null,
        public ?string $host = null,
        public ?string $login = null,
        public ?string $statusMessage = null,
    ) {}

    /**
     * Create from array.
     *
     * @param  array{isAuthenticated: bool, authType?: string, host?: string, login?: string, statusMessage?: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            isAuthenticated: $data['isAuthenticated'],
            authType: $data['authType'] ?? null,
            host: $data['host'] ?? null,
            login: $data['login'] ?? null,
            statusMessage: $data['statusMessage'] ?? null,
        );
    }

    /**
     * Convert to array.
     *
     * @return array{isAuthenticated: bool, authType?: string, host?: string, login?: string, statusMessage?: string}
     */
    public function toArray(): array
    {
        return array_filter([
            'isAuthenticated' => $this->isAuthenticated,
            'authType' => $this->authType,
            'host' => $this->host,
            'login' => $this->login,
            'statusMessage' => $this->statusMessage,
        ], fn ($v) => $v !== null);
    }
}
