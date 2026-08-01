<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to reset session-scoped permission approvals.
 */
readonly class PermissionsResetSessionApprovalsRequest implements Arrayable
{
    /**
     * @param  ?bool  $includeLocation  Whether to also reset location-scoped approvals.
     */
    public function __construct(
        public ?bool $includeLocation = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            includeLocation: $data['includeLocation'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'includeLocation' => $this->includeLocation,
        ], fn ($v) => $v !== null);
    }
}
