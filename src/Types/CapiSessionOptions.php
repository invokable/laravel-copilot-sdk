<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\FusionTier;

/**
 * Provider-scoped options for the Copilot API (CAPI).
 *
 * These settings apply to the built-in Copilot API provider only. They live
 * under their own namespace because a single session can host multiple
 * providers (CAPI alongside BYOK via {@see ProviderConfig}), so transport and
 * provider-level choices are conceptually per-provider rather than global.
 */
readonly class CapiSessionOptions implements Arrayable
{
    /**
     * @param  ?bool  $enableWebSocketResponses  Whether to use the WebSocket transport for the CAPI Responses API.
     *                                           WebSocket transport is enabled by default whenever the selected model
     *                                           advertises the `ws:/responses` endpoint. Set this to `false` to fall back
     *                                           to the HTTP Responses transport instead — useful for users behind proxies
     *                                           where WebSocket connections fail.
     *
     *                                           Setting this to `false` is equivalent to setting the
     *                                           `COPILOT_CLI_DISABLE_WEBSOCKET_RESPONSES` environment variable.
     * @param  AutoTier|string|null  $autoTier  Routing preference when the automatic model is selected.
     * @param  FusionTier|string|null  $fusionTier  Copilot Fusion tier when the `fusion` or `auto` model is selected. Cannot be combined with $autoTier.
     */
    public function __construct(
        public ?bool $enableWebSocketResponses = null,
        public AutoTier|string|null $autoTier = null,
        public FusionTier|string|null $fusionTier = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            enableWebSocketResponses: $data['enableWebSocketResponses'] ?? null,
            autoTier: isset($data['autoTier'])
                ? (is_string($data['autoTier']) ? (AutoTier::tryFrom($data['autoTier']) ?? $data['autoTier']) : $data['autoTier'])
                : null,
            fusionTier: isset($data['fusionTier'])
                ? (is_string($data['fusionTier']) ? (FusionTier::tryFrom($data['fusionTier']) ?? $data['fusionTier']) : $data['fusionTier'])
                : null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'enableWebSocketResponses' => $this->enableWebSocketResponses,
            'autoTier' => $this->autoTier instanceof AutoTier ? $this->autoTier->value : $this->autoTier,
            'fusionTier' => $this->fusionTier instanceof FusionTier ? $this->fusionTier->value : $this->fusionTier,
        ], fn ($value) => $value !== null);
    }
}
