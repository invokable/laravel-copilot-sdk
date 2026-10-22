<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use Revolution\Copilot\Enums\HostAuthenticationMode;

/** Host-wide AHP admission authentication policy. */
readonly class HostAuthentication implements Arrayable
{
    public function __construct(
        public HostAuthenticationMode $mode,
        public ?string $requireIdentity = null,
    ) {
        if ($mode === HostAuthenticationMode::Custom
            && (! is_string($requireIdentity) || trim($requireIdentity) === '')) {
            throw new InvalidArgumentException('Custom host authentication requires a nonempty requireIdentity.');
        }

        if ($mode !== HostAuthenticationMode::Custom && $requireIdentity !== null) {
            throw new InvalidArgumentException('requireIdentity is only valid for custom host authentication.');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            mode: HostAuthenticationMode::from($data['mode'] ?? ''),
            requireIdentity: $data['requireIdentity'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mode' => $this->mode->value,
            'requireIdentity' => $this->requireIdentity,
        ], fn ($value) => $value !== null);
    }
}
