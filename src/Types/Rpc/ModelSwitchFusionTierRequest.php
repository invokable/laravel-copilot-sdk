<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FusionTier;
use Revolution\Copilot\Enums\ModelChangeSource;

/**
 * A Copilot Fusion tier request for the session, named as the CLI's `--fusion-tier` names it.
 * This updates Auto configuration only; it does not change the selected model to `auto`.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ModelSwitchFusionTierRequest implements Arrayable
{
    /**
     * @param  FusionTier|string  $fusionTier  Copilot Fusion tier to activate when a future user turn using the `auto` model safely mints a replacement model and token pair.
     * @param  ModelChangeSource|string|null  $source  Origin to record on the effective `session.model_change` event. Defaults to `sdk` when omitted.
     */
    public function __construct(
        public FusionTier|string $fusionTier,
        public ModelChangeSource|string|null $source = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $fusionTier = Arr::string($data, 'fusionTier');

        return new self(
            fusionTier: FusionTier::tryFrom($fusionTier) ?? $fusionTier,
            source: isset($data['source']) ? (ModelChangeSource::tryFrom($data['source']) ?? $data['source']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'fusionTier' => $this->fusionTier instanceof FusionTier ? $this->fusionTier->value : $this->fusionTier,
            ...array_filter([
                'source' => $this->source instanceof ModelChangeSource ? $this->source->value : $this->source,
            ], fn ($v) => $v !== null),
        ];
    }
}
