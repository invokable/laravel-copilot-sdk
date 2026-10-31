<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * One key to remove from a settings file, named exactly as it appears in the file.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SettingsRemoveRawKeyRequest implements Arrayable
{
    public function __construct(
        public string $filePath,
        public string $rawKey,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            filePath: Arr::string($data, 'filePath', ''),
            rawKey: Arr::string($data, 'rawKey', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'filePath' => $this->filePath,
            'rawKey' => $this->rawKey,
        ];
    }
}
