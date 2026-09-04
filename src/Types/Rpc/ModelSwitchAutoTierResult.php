<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\ModelSwitchAutoTierStatus;

/**
 * Result of requesting an Auto routing preference switch.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ModelSwitchAutoTierResult implements Arrayable
{
    /**
     * @param  ModelSwitchAutoTierStatus|string  $status  Whether the request was accepted as `pending` or already `unchanged`.
     * @param  AutoTier|string|null  $activatingAutoTier  Auto preference currently claimed by an in-progress activation.
     * @param  AutoTier|string|null  $effectiveAutoTier  Auto preference currently committed for the session.
     * @param  AutoTier|string|null  $pendingAutoTier  Latest unclaimed Auto preference waiting for a future user turn.
     * @param  AutoTier|string|null  $supersededAutoTier  Auto preference this request replaced, if any.
     */
    public function __construct(
        public ModelSwitchAutoTierStatus|string $status,
        public AutoTier|string|null $activatingAutoTier = null,
        public AutoTier|string|null $effectiveAutoTier = null,
        public AutoTier|string|null $pendingAutoTier = null,
        public AutoTier|string|null $supersededAutoTier = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: ModelSwitchAutoTierStatus::tryFrom($data['status'] ?? '') ?? ($data['status'] ?? ''),
            activatingAutoTier: isset($data['activatingAutoTier']) ? (AutoTier::tryFrom($data['activatingAutoTier']) ?? $data['activatingAutoTier']) : null,
            effectiveAutoTier: isset($data['effectiveAutoTier']) ? (AutoTier::tryFrom($data['effectiveAutoTier']) ?? $data['effectiveAutoTier']) : null,
            pendingAutoTier: isset($data['pendingAutoTier']) ? (AutoTier::tryFrom($data['pendingAutoTier']) ?? $data['pendingAutoTier']) : null,
            supersededAutoTier: isset($data['supersededAutoTier']) ? (AutoTier::tryFrom($data['supersededAutoTier']) ?? $data['supersededAutoTier']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status instanceof ModelSwitchAutoTierStatus ? $this->status->value : $this->status,
            'activatingAutoTier' => $this->activatingAutoTier instanceof AutoTier ? $this->activatingAutoTier->value : $this->activatingAutoTier,
            'effectiveAutoTier' => $this->effectiveAutoTier instanceof AutoTier ? $this->effectiveAutoTier->value : $this->effectiveAutoTier,
            'pendingAutoTier' => $this->pendingAutoTier instanceof AutoTier ? $this->pendingAutoTier->value : $this->pendingAutoTier,
            'supersededAutoTier' => $this->supersededAutoTier instanceof AutoTier ? $this->supersededAutoTier->value : $this->supersededAutoTier,
        ], fn ($v) => $v !== null);
    }
}
