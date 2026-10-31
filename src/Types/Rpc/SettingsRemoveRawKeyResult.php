<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * The outcome of the settings-file edit.
 *
 * `status` is `ok` when the edit completed, including when the key was absent.
 * `invalid` means the existing file could not be parsed and was left unchanged.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SettingsRemoveRawKeyResult implements Arrayable
{
    public function __construct(
        public string $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: Arr::string($data, 'status', ''),
        );
    }

    public function toArray(): array
    {
        return ['status' => $this->status];
    }
}
