<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request to update whether remote clients may create new sessions. */
readonly class HostSetSessionCreationPolicyRequest implements Arrayable
{
    public function __construct(
        public string $hostId,
        public bool $allowCreatingNewSessions,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hostId: $data['hostId'] ?? '',
            allowCreatingNewSessions: $data['allowCreatingNewSessions'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'hostId' => $this->hostId,
            'allowCreatingNewSessions' => $this->allowCreatingNewSessions,
        ];
    }
}
