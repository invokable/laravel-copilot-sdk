<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional context used when ensuring a local session workspace.
 *
 * @experimental
 */
readonly class WorkspacesEnsureRequest implements Arrayable
{
    public function __construct(
        public mixed $context = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(context: $data['context'] ?? null);
    }

    public function toArray(): array
    {
        return $this->context === null ? [] : ['context' => $this->context];
    }
}
