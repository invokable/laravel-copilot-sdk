<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Session's model-facing additional directory ledger.
 *
 * @experimental
 */
readonly class PermissionsAdditionalDirectoriesListResult implements Arrayable
{
    /**
     * @param  list<string>  $directories  Additional directories included in the session's model context.
     */
    public function __construct(
        public array $directories,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            directories: Arr::array($data, 'directories', []),
        );
    }

    public function toArray(): array
    {
        return [
            'directories' => $this->directories,
        ];
    }
}
