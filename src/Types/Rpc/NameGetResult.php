<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of getting the session name.
 */
readonly class NameGetResult implements Arrayable
{
    /**
     * @param  ?string  $name  The session name, falling back to the auto-generated summary, or null if neither exists
     */
    public function __construct(
        public ?string $name = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
