<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to set approve-all mode for session permissions.
 */
readonly class PermissionsSetApproveAllRequest implements Arrayable
{
    /**
     * @param  bool  $enabled  Whether to auto-approve all tool permission requests.
     */
    public function __construct(
        public bool $enabled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enabled: $data['enabled'],
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
        ];
    }
}
