<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use SensitiveParameter;

/**
 * Host response to a shell authentication request.
 */
readonly class ShellAuthenticationResponse implements Arrayable
{
    public function __construct(
        public string $action,
        #[SensitiveParameter]
        private ?string $value = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            action: is_string($data['action'] ?? null) ? $data['action'] : '',
            value: is_string($data['value'] ?? null) ? $data['value'] : null,
        );
    }

    public function toArray(): array
    {
        if ($this->action === 'accept') {
            return ['action' => 'accept', 'value' => $this->value];
        }

        return ['action' => $this->action];
    }
}
