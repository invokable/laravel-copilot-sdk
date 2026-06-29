<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for disabling an extension.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ExtensionsDisableRequest implements Arrayable
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
            id: Arr::string($data, 'id'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
