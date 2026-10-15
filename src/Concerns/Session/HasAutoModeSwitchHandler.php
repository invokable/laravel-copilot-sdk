<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use InvalidArgumentException;
use Revolution\Copilot\Enums\UIAutoModeSwitchResponse;
use Revolution\Copilot\Types\AutoModeSwitchRequest;

/**
 * Manages auto-mode-switch request handler registration and execution.
 *
 * Handles incoming `autoModeSwitch.request` reverse-RPC requests.
 *
 * @internal
 */
trait HasAutoModeSwitchHandler
{
    /**
     * Auto-mode-switch handler.
     *
     * @var Closure(AutoModeSwitchRequest): UIAutoModeSwitchResponse|string|null
     */
    protected ?Closure $autoModeSwitchHandler = null;

    /**
     * Register an auto-mode-switch handler.
     *
     * @param  Closure(AutoModeSwitchRequest): UIAutoModeSwitchResponse|string|null  $handler  Return "yes", "yes_always", or "no".
     *
     * @internal
     */
    public function registerAutoModeSwitchHandler(?Closure $handler): void
    {
        $this->autoModeSwitchHandler = $handler;
    }

    /**
     * Handle an autoModeSwitch.request reverse-RPC request.
     *
     * @internal
     */
    public function handleAutoModeSwitchRequest(AutoModeSwitchRequest $request): UIAutoModeSwitchResponse
    {
        if ($this->autoModeSwitchHandler === null) {
            return UIAutoModeSwitchResponse::No;
        }

        /* @var UIAutoModeSwitchResponse|string|null $response */
        $response = ($this->autoModeSwitchHandler)($request);

        if ($response === null) {
            return UIAutoModeSwitchResponse::No;
        }

        if ($response instanceof UIAutoModeSwitchResponse) {
            return $response;
        }

        return UIAutoModeSwitchResponse::tryFrom($response)
            ?? throw new InvalidArgumentException('Invalid auto mode switch response');
    }
}
