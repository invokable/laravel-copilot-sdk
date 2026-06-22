<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

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
     */
    public function __construct(
        public ?bool $enableWebSocketResponses = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            enableWebSocketResponses: $data['enableWebSocketResponses'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'enableWebSocketResponses' => $this->enableWebSocketResponses,
        ], fn ($value) => $value !== null);
    }
}
