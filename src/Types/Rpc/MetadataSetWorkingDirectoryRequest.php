<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for updating a session working directory.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class MetadataSetWorkingDirectoryRequest implements Arrayable
{
    public function __construct(
        public string $workingDirectory,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            workingDirectory: $data['workingDirectory'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'workingDirectory' => $this->workingDirectory,
        ];
    }
}
