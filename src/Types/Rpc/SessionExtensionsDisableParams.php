<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for disabling an extension.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionExtensionsDisableParams implements Arrayable
{
    /**
     * @param  string  $id  Source-qualified extension ID to disable
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
