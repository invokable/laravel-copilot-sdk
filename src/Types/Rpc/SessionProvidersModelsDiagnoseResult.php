<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Cached availability of a registered provider model.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionProvidersModelsDiagnoseResult implements Arrayable
{
    /**
     * @param  bool|null  $available  Whether the model is selectable; null when no row exists.
     */
    public function __construct(
        public ?bool $available = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            available: isset($data['available']) ? (bool) $data['available'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'available' => $this->available,
        ], fn ($value) => $value !== null);
    }
}
