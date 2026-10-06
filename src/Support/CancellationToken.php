<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

use Closure;

/**
 * Cooperative cancellation signal for inbound JSON-RPC provider callbacks.
 */
final class CancellationToken
{
    protected bool $cancelled = false;

    /**
     * @var array<int, Closure(): void>
     */
    protected array $listeners = [];

    protected int $nextListenerId = 0;

    public function isCancellationRequested(): bool
    {
        return $this->cancelled;
    }

    /**
     * Register a callback to run when cancellation is requested.
     *
     * @return Closure(): void Unregisters the callback.
     */
    public function onCancellationRequested(Closure $callback): Closure
    {
        if ($this->cancelled) {
            $callback();

            return static function (): void {};
        }

        $id = $this->nextListenerId++;
        $this->listeners[$id] = $callback;

        return function () use ($id): void {
            unset($this->listeners[$id]);
        };
    }

    /**
     * @internal
     */
    public function cancel(): void
    {
        if ($this->cancelled) {
            return;
        }

        $this->cancelled = true;
        $listeners = $this->listeners;
        $this->listeners = [];

        foreach ($listeners as $listener) {
            $listener();
        }
    }
}
