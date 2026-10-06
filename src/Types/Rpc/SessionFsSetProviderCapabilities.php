<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional capabilities declared by a SessionFs provider.
 */
readonly class SessionFsSetProviderCapabilities implements Arrayable
{
    /**
     * @param  ?bool  $sqlite  Whether the provider supports SQLite query/exists operations.
     *                         When false or omitted, the runtime will not offer SQL tools or
     *                         todo tracking for sessions using this provider.
     * @param  ?bool  $binary  Whether the provider supports exact binary reads and writes.
     *                         Requires both readFileBytes and writeFileBytes callbacks.
     */
    public function __construct(
        public ?bool $sqlite = null,
        public ?bool $binary = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sqlite: isset($data['sqlite']) ? (bool) $data['sqlite'] : null,
            binary: isset($data['binary']) ? (bool) $data['binary'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sqlite' => $this->sqlite,
            'binary' => $this->binary,
        ], fn ($v) => $v !== null);
    }
}
