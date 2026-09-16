<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Source and destination paths for a rename within the session workspace files directory.
 *
 * @experimental
 */
readonly class WorkspacesRenamePathRequest implements Arrayable
{
    /**
     * @param  string  $source  Slash-separated source path relative to the workspace files directory
     * @param  string  $destination  Slash-separated destination path relative to the workspace files directory
     */
    public function __construct(
        public string $source,
        public string $destination,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            source: Arr::string($data, 'source'),
            destination: Arr::string($data, 'destination'),
        );
    }

    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'destination' => $this->destination,
        ];
    }
}
