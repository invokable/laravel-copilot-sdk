<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ExitPlanModeRequest;
use Revolution\Copilot\Types\ExitPlanModeResult;

/**
 * Manages exit-plan-mode request handler registration and execution.
 *
 * Handles incoming `exitPlanMode.request` reverse-RPC requests.
 *
 * @internal
 */
trait HasExitPlanModeHandler
{
    /**
     * Exit-plan-mode handler.
     *
     * @var Closure(ExitPlanModeRequest): ExitPlanModeResult|array|null
     */
    protected ?Closure $exitPlanModeHandler = null;

    /**
     * Register an exit-plan-mode handler.
     *
     * @param  Closure(ExitPlanModeRequest): ExitPlanModeResult|array|null  $handler
     *
     * @internal
     */
    public function registerExitPlanModeHandler(?Closure $handler): void
    {
        $this->exitPlanModeHandler = $handler;
    }

    /**
     * Handle an exitPlanMode.request reverse-RPC request.
     *
     * @internal
     */
    public function handleExitPlanModeRequest(ExitPlanModeRequest $request): ExitPlanModeResult
    {
        if ($this->exitPlanModeHandler === null) {
            return new ExitPlanModeResult(approved: true);
        }

        $result = ($this->exitPlanModeHandler)($request);

        if ($result instanceof ExitPlanModeResult) {
            return $result;
        }

        return $result instanceof Arrayable
            ? ExitPlanModeResult::fromArray($result->toArray())
            : (is_array($result) ? ExitPlanModeResult::fromArray($result) : new ExitPlanModeResult(approved: true));
    }
}
