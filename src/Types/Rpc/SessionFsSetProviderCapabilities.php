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
     */
    public function __construct(
        public ?bool $sqlite = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sqlite: isset($data['sqlite']) ? (bool) $data['sqlite'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sqlite' => $this->sqlite,
        ], fn ($v) => $v !== null);
    }
}
