<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\ContextTier;

/** Typed effective managed settings values. */
readonly class ManagedSettingsValues implements Arrayable
{
    public function __construct(
        public ?string $model = null,
        public AutoTier|string|null $autoTier = null,
        public ContextTier|string|null $contextTier = null,
        public ?string $effortLevel = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $autoTier = $data['autoTier'] ?? null;

        if ($autoTier !== null && ! is_string($autoTier)) {
            throw new \InvalidArgumentException('Managed settings autoTier must be a string or null.');
        }
        $contextTier = $data['contextTier'] ?? null;
        if ($contextTier !== null && ! is_string($contextTier)) {
            throw new \InvalidArgumentException('Managed settings contextTier must be a string or null.');
        }
        $effortLevel = $data['effortLevel'] ?? null;
        if ($effortLevel !== null && ! is_string($effortLevel)) {
            throw new \InvalidArgumentException('Managed settings effortLevel must be a string or null.');
        }

        return new self(
            model: $data['model'] ?? null,
            autoTier: $autoTier !== null ? (AutoTier::tryFrom($autoTier) ?? $autoTier) : null,
            contextTier: $contextTier !== null ? (ContextTier::tryFrom($contextTier) ?? $contextTier) : null,
            effortLevel: $effortLevel,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'model' => $this->model,
            'autoTier' => $this->autoTier instanceof AutoTier ? $this->autoTier->value : $this->autoTier,
            'contextTier' => $this->contextTier instanceof ContextTier ? $this->contextTier->value : $this->contextTier,
            'effortLevel' => $this->effortLevel,
        ], static fn ($value) => $value !== null);
    }
}
