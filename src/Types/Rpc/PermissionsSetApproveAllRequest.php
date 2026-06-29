<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\PermissionsSetApproveAllSource;

/**
 * Request to set approve-all mode for session permissions.
 */
readonly class PermissionsSetApproveAllRequest implements Arrayable
{
    /**
     * @param  bool  $enabled  Whether to auto-approve all tool permission requests.
     * @param  PermissionsSetApproveAllSource|string|null  $source  Optional source for telemetry
     */
    public function __construct(
        public bool $enabled,
        public PermissionsSetApproveAllSource|string|null $source = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enabled: Arr::boolean($data, 'enabled'),
            source: isset($data['source']) ? PermissionsSetApproveAllSource::tryFrom($data['source']) ?? $data['source'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'source' => $this->source instanceof PermissionsSetApproveAllSource ? $this->source->value : $this->source,
        ], fn ($value): bool => $value !== null);
    }
}
