<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Client;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Types\ModelInfo;

use function Illuminate\Support\defer;

/**
 * Model listing with caching and BYOK (Bring Your Own Key) support.
 */
trait ManagesModels
{
    /**
     * Custom handler for listing available models (for BYOK mode).
     * When set, listModels() calls this instead of querying the CLI server.
     */
    protected $onListModels = null;

    /**
     * Set a custom handler for listing available models (for BYOK mode).
     *
     * When set, listModels() calls this callback instead of querying the CLI server.
     * Pass null to remove the handler and revert to the default CLI server behaviour.
     *
     * ```php
     * $models = Copilot::client()->listModelsUsing(fn() => [])->listModels();
     * ```
     */
    public function listModelsUsing(?callable $callback = null): static
    {
        $this->onListModels = $callback;

        return $this;
    }

    /**
     * List available models with their metadata.
     *
     * If a handler was provided via listModelsUsing(), it is called instead of
     * querying the CLI server. Useful in BYOK mode to return models available
     * from your custom provider.
     *
     * Results are cached after the first successful call to avoid rate limiting.
     * The cache is cleared when the client disconnects.
     *
     * @return array<ModelInfo>
     *
     * @throws JsonRpcException|LockTimeoutException
     */
    public function listModels(): array
    {
        if ($this->onListModels !== null) {
            $models = ($this->onListModels)();

            return array_map(
                fn (array $model) => ModelInfo::fromArray($model),
                $models,
            );
        }

        $this->ensureConnected();

        // Create a cache key based on options to prevent conflicts between different users' settings.
        $cache_key = md5(json_encode($this->options));

        $modelsData = Cache::lock('copilot-models-lock', 10)->block(5, function () use ($cache_key) {
            return Cache::remember('copilot-models-cache:'.$cache_key, now()->plus(minutes: 5), function () {
                $response = $this->rpcClient->request('models.list');

                return $response['models'] ?? [];
            });
        });

        defer(fn () => Cache::forget('copilot-models-cache:'.$cache_key));

        return array_map(
            fn (array $model) => ModelInfo::fromArray($model),
            $modelsData,
        );
    }
}
