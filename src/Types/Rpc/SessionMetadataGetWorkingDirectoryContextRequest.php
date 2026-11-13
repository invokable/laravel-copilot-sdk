<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for retrieving a session's working-directory context.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionMetadataGetWorkingDirectoryContextRequest implements Arrayable
{
    public function __construct(
        public string $cwd,
        public ?bool $includeDefaultBranch = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cwd: Arr::string($data, 'cwd', ''),
            includeDefaultBranch: isset($data['includeDefaultBranch'])
                ? Arr::boolean($data, 'includeDefaultBranch')
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cwd' => $this->cwd,
            'includeDefaultBranch' => $this->includeDefaultBranch,
        ], fn ($value): bool => $value !== null);
    }
}
