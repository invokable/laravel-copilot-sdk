<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

/**
 * Host application's Microsoft Entra registration.
 */
readonly class EntraAuthConfig implements Arrayable
{
    /**
     * @param  string  $clientId  Application (client) ID of the host's Microsoft Entra registration.
     *
     * @throws InvalidArgumentException When the client ID is blank.
     */
    public function __construct(
        public string $clientId,
    ) {
        if (trim($this->clientId) === '') {
            throw new InvalidArgumentException('entra.clientId must be a non-empty string.');
        }
    }

    public static function fromArray(array $data): self
    {
        $clientId = $data['clientId'] ?? null;

        if (! is_string($clientId)) {
            throw new InvalidArgumentException('entra.clientId must be a non-empty string.');
        }

        return new self($clientId);
    }

    public function toArray(): array
    {
        return ['clientId' => $this->clientId];
    }
}
