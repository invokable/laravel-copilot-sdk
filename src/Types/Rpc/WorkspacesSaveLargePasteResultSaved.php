<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Descriptor for the saved paste file.
 *
 * @experimental
 */
readonly class WorkspacesSaveLargePasteResultSaved implements Arrayable
{
    public function __construct(
        public string $filename,
        public string $filePath,
        public int $sizeBytes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            filename: Arr::string($data, 'filename'),
            filePath: Arr::string($data, 'filePath'),
            sizeBytes: Arr::integer($data, 'sizeBytes'),
        );
    }

    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'filePath' => $this->filePath,
            'sizeBytes' => $this->sizeBytes,
        ];
    }
}
