<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ExitPlanModeRequest;
use Revolution\Copilot\Types\ExitPlanModeResult;
use Throwable;

/**
 * Manages exit-plan-mode request handler registration and execution.
 *
 * When a registered handler exists, incoming `exit_plan_mode.requested` broadcast events
 * are forwarded to the handler. The handler's response determines whether the plan
 * exit is approved.
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
     * Handle an exit_plan_mode.requested broadcast event.
     *
     * Invokes the registered handler and responds via session.handleExitPlanMode RPC.
     *
     * @internal
     */
    protected function handleExitPlanModeRequest(ExitPlanModeRequest $request, string $requestId): void
    {
        if ($this->exitPlanModeHandler === null) {
            return;
        }

        try {
            $result = ($this->exitPlanModeHandler)($request);

            $resultArray = match (true) {
                $result instanceof Arrayable => $result->toArray(),
                is_array($result) => $result,
                default => ['approved' => true],
            };

            $this->client->request('session.handleExitPlanMode', [
                'sessionId' => $this->sessionId,
                'requestId' => $requestId,
                'result' => $resultArray,
            ]);
        } catch (Throwable) {
            // Handler failed — approve by default so the session doesn't hang
            try {
                $this->client->request('session.handleExitPlanMode', [
                    'sessionId' => $this->sessionId,
                    'requestId' => $requestId,
                    'result' => ['approved' => true],
                ]);
            } catch (Throwable) {
                // Connection lost — nothing we can do
            }
        }
    }
}
