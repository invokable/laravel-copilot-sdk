<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The servers retained by the session after warmup.
 *
 * @experimental
 */
readonly class SessionLspStartResult implements Arrayable
{
    /**
     * @param  string[]  $servers
     * @param  LspWarmupError[]  $errors
     */
    public function __construct(
        public array $servers = [],
        public array $errors = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            servers: $data['servers'] ?? [],
            errors: array_map(
                static fn (array $error): LspWarmupError => LspWarmupError::fromArray($error),
                $data['errors'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'servers' => $this->servers,
            'errors' => array_map(static fn (LspWarmupError $error): array => $error->toArray(), $this->errors),
        ];
    }
}
