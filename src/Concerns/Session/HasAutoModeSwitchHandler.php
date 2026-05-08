<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Revolution\Copilot\Types\AutoModeSwitchRequest;
use Throwable;

/**
 * Manages auto-mode-switch request handler registration and execution.
 *
 * When a registered handler exists, incoming `auto_mode_switch.requested` broadcast events
 * are forwarded to the handler. The handler returns "yes", "yes_always", or "no".
 *
 * @internal
 */
trait HasAutoModeSwitchHandler
{
    /**
     * Auto-mode-switch handler.
     *
     * @var Closure(AutoModeSwitchRequest): string|null
     */
    protected ?Closure $autoModeSwitchHandler = null;

    /**
     * Register an auto-mode-switch handler.
     *
     * @param  Closure(AutoModeSwitchRequest): string|null  $handler  Return "yes", "yes_always", or "no".
     *
     * @internal
     */
    public function registerAutoModeSwitchHandler(?Closure $handler): void
    {
        $this->autoModeSwitchHandler = $handler;
    }

    /**
     * Handle an auto_mode_switch.requested broadcast event.
     *
     * Invokes the registered handler and responds via session.handleAutoModeSwitch RPC.
     *
     * @internal
     */
    protected function handleAutoModeSwitchRequest(AutoModeSwitchRequest $request, string $requestId): void
    {
        if ($this->autoModeSwitchHandler === null) {
            return;
        }

        try {
            $response = ($this->autoModeSwitchHandler)($request);

            $this->client->request('session.handleAutoModeSwitch', [
                'sessionId' => $this->sessionId,
                'requestId' => $requestId,
                'response' => $response,
            ]);
        } catch (Throwable) {
            try {
                $this->client->request('session.handleAutoModeSwitch', [
                    'sessionId' => $this->sessionId,
                    'requestId' => $requestId,
                    'response' => 'no',
                ]);
            } catch (Throwable) {
                // Connection lost — nothing we can do
            }
        }
    }
}
