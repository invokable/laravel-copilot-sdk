<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Cached provider identity available for an interactive login.
 */
readonly class AuthLoginIdentity implements Arrayable
{
    /**
     * @param  string  $accountId  Opaque provider-owned identity ID. Pass it back unchanged; do not parse it.
     */
    public function __construct(
        public string $accountId,
        public string $label,
        public bool $active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accountId: $data['accountId'] ?? '',
            label: $data['label'] ?? '',
            active: (bool) ($data['active'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'accountId' => $this->accountId,
            'label' => $this->label,
            'active' => $this->active,
        ];
    }
}
