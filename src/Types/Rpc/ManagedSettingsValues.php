<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AutoTier;

/** Typed effective managed settings values. */
readonly class ManagedSettingsValues implements Arrayable
{
    public function __construct(
        public ?string $model = null,
        public AutoTier|string|null $autoTier = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $autoTier = $data['autoTier'] ?? null;

        if ($autoTier !== null && ! is_string($autoTier)) {
            throw new \InvalidArgumentException('Managed settings autoTier must be a string or null.');
        }

        return new self(
            model: $data['model'] ?? null,
            autoTier: $autoTier !== null ? (AutoTier::tryFrom($autoTier) ?? $autoTier) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'model' => $this->model,
            'autoTier' => $this->autoTier instanceof AutoTier ? $this->autoTier->value : $this->autoTier,
        ], static fn ($value) => $value !== null);
    }
}
