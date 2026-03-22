<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

use Closure;
use OpenTelemetry\API\Globals;
use OpenTelemetry\Context\Context;

/**
 * W3C Trace Context helpers for OpenTelemetry propagation.
 *
 * Mirrors the Python SDK's `_telemetry.py` pattern:
 * - `get()` extracts current trace context (like `get_trace_context()`)
 * - `restore()` / `detach()` restore inbound context (like `trace_context()` context manager)
 *
 * When `open-telemetry/api` is installed, context propagation is automatic.
 * Without it, these methods are safe no-ops.
 */
class TraceContext
{
    /**
     * User-provided trace context provider callback.
     *
     * @var ?Closure(): array{traceparent?: string, tracestate?: string}
     */
    protected static ?Closure $provider = null;

    /**
     * Set a custom trace context provider.
     *
     * @param  ?Closure(): array{traceparent?: string, tracestate?: string}  $provider
     */
    public static function useProvider(?Closure $provider): void
    {
        static::$provider = $provider;
    }

    /**
     * Get the current W3C Trace Context (traceparent/tracestate).
     *
     * If a custom provider is set, it is used. Otherwise, if `open-telemetry/api`
     * is installed, the current context is extracted automatically.
     *
     * @return array{traceparent?: string, tracestate?: string}
     */
    public static function get(): array
    {
        if (static::$provider !== null) {
            try {
                return (static::$provider)();
            } catch (\Throwable) {
                return [];
            }
        }

        return static::getFromOpenTelemetry();
    }

    /**
     * Restore inbound W3C Trace Context as the active OpenTelemetry context.
     *
     * Returns a scope token that must be passed to `detach()` when done.
     * Returns null if OpenTelemetry is not available or no traceparent is provided.
     *
     * @return mixed Scope token for detach(), or null
     */
    public static function restore(?string $traceparent, ?string $tracestate = null): mixed
    {
        if ($traceparent === null) {
            return null;
        }

        if (! static::isOpenTelemetryAvailable()) {
            return null;
        }

        try {
            $carrier = ['traceparent' => $traceparent];
            if ($tracestate !== null) {
                $carrier['tracestate'] = $tracestate;
            }

            $propagator = Globals::propagator();
            $context = $propagator->extract($carrier);

            return Context::storage()->attach($context);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Detach a previously restored trace context scope.
     */
    public static function detach(mixed $scope): void
    {
        if ($scope === null) {
            return;
        }

        try {
            $scope->detach();
        } catch (\Throwable) {
            //
        }
    }

    /**
     * Extract trace context from OpenTelemetry API if available.
     *
     * @return array{traceparent?: string, tracestate?: string}
     */
    protected static function getFromOpenTelemetry(): array
    {
        if (! static::isOpenTelemetryAvailable()) {
            return [];
        }

        try {
            $carrier = [];
            $propagator = Globals::propagator();
            $propagator->inject($carrier);

            $result = [];
            if (isset($carrier['traceparent'])) {
                $result['traceparent'] = $carrier['traceparent'];
            }
            if (isset($carrier['tracestate'])) {
                $result['tracestate'] = $carrier['tracestate'];
            }

            return $result;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Check if the OpenTelemetry API is available.
     */
    public static function isOpenTelemetryAvailable(): bool
    {
        return class_exists(Globals::class);
    }
}
