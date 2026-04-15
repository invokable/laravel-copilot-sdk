<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for enabling an extension.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ExtensionsEnableRequest implements Arrayable
{
    /**
     * @param  string  $id  Source-qualified extension ID to enable
     */
    public function __construct(
        public string $id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
