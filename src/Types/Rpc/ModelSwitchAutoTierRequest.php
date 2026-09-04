<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\ModelChangeSource;

/**
 * An Auto preference request for the session. This updates Auto configuration
 * only; it does not change the selected model to `auto`.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ModelSwitchAutoTierRequest implements Arrayable
{
    /**
     * @param  AutoTier|string|null  $autoTier  Auto preference to activate when a future user turn using the `auto` model safely mints a replacement model and token pair. Null returns to provider-default Auto routing.
     * @param  ModelChangeSource|string|null  $source  Origin to record on the effective `session.model_change` event. Defaults to `sdk` when omitted.
     */
    public function __construct(
        public AutoTier|string|null $autoTier = null,
        public ModelChangeSource|string|null $source = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            autoTier: isset($data['autoTier']) ? (AutoTier::tryFrom($data['autoTier']) ?? $data['autoTier']) : null,
            source: isset($data['source']) ? (ModelChangeSource::tryFrom($data['source']) ?? $data['source']) : null,
        );
    }

    public function toArray(): array
    {
        // autoTier is always sent, even when null, so the runtime can distinguish
        // "return to provider-default routing" from "no preference specified".
        return [
            'autoTier' => $this->autoTier instanceof AutoTier ? $this->autoTier->value : $this->autoTier,
            ...array_filter([
                'source' => $this->source instanceof ModelChangeSource ? $this->source->value : $this->source,
            ], fn ($v) => $v !== null),
        ];
    }
}
