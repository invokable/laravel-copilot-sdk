<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for setting the session name.
 */
readonly class NameSetRequest implements Arrayable
{
    /**
     * @param  string  $name  New session name (1–100 characters, trimmed of leading/trailing whitespace)
     */
    public function __construct(
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
