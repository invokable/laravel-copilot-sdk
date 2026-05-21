<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for session.auth.setCredentials.
 */
readonly class SessionSetCredentialsParams implements Arrayable
{
    public function __construct(
        public ?AuthInfo $credentials = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            credentials: isset($data['credentials']) ? AuthInfo::fromArray($data['credentials']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'credentials' => $this->credentials?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
