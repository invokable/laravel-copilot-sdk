<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of checking file existence via SessionFs.
 */
readonly class SessionFsExistsResult implements Arrayable
{
    /**
     * @param  bool  $exists  Whether the path exists
     */
    public function __construct(
        public bool $exists,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            exists: $data['exists'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'exists' => $this->exists,
        ];
    }
}
