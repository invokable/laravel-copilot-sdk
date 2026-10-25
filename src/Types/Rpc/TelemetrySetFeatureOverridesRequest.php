<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Feature overrides to attach to subsequent telemetry events for a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TelemetrySetFeatureOverridesRequest implements Arrayable
{
    /**
     * @param  array<string, string>  $features  Feature overrides, replacing any previously set overrides.
     */
    public function __construct(
        public array $features,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            features: Arr::array($data, 'features', []),
        );
    }

    public function toArray(): array
    {
        return [
            'features' => $this->features,
        ];
    }
}
