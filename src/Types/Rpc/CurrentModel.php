<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AutoTier;

/**
 * Result of getting current model for a session.
 */
readonly class CurrentModel implements Arrayable
{
    /**
     * @param  ?string  $modelId  The currently active model ID, or null if using the default model.
     * @param  ?string  $reasoningEffort  Reasoning effort level currently applied to the active model.
     * @param  ?string  $contextTier  Context tier currently pinned for the session ("default" or "long_context").
     * @param  AutoTier|string|null  $autoTier  Auto preference currently committed for the session.
     * @param  AutoTier|string|null  $activatingAutoTier  Auto preference currently claimed by an in-progress activation.
     * @param  AutoTier|string|null  $pendingAutoTier  Latest unclaimed Auto preference waiting for a future user turn.
     */
    public function __construct(
        public ?string $modelId = null,
        public ?string $reasoningEffort = null,
        public ?string $contextTier = null,
        public AutoTier|string|null $autoTier = null,
        public AutoTier|string|null $activatingAutoTier = null,
        public AutoTier|string|null $pendingAutoTier = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: $data['modelId'] ?? null,
            reasoningEffort: $data['reasoningEffort'] ?? null,
            contextTier: $data['contextTier'] ?? null,
            autoTier: isset($data['autoTier']) ? (AutoTier::tryFrom($data['autoTier']) ?? $data['autoTier']) : null,
            activatingAutoTier: isset($data['activatingAutoTier']) ? (AutoTier::tryFrom($data['activatingAutoTier']) ?? $data['activatingAutoTier']) : null,
            pendingAutoTier: isset($data['pendingAutoTier']) ? (AutoTier::tryFrom($data['pendingAutoTier']) ?? $data['pendingAutoTier']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'modelId' => $this->modelId,
            'reasoningEffort' => $this->reasoningEffort,
            'contextTier' => $this->contextTier,
            'autoTier' => $this->autoTier instanceof AutoTier ? $this->autoTier->value : $this->autoTier,
            'activatingAutoTier' => $this->activatingAutoTier instanceof AutoTier ? $this->activatingAutoTier->value : $this->activatingAutoTier,
            'pendingAutoTier' => $this->pendingAutoTier instanceof AutoTier ? $this->pendingAutoTier->value : $this->pendingAutoTier,
        ], fn ($v) => $v !== null);
    }
}
