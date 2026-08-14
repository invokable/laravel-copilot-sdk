<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Revolution\Copilot\Support\PermissionDecision;
use Revolution\Copilot\Types\Rpc\PermissionDecisionContext;
use Revolution\Copilot\Types\Rpc\PermissionDecisionRequest;
use Throwable;

/**
 * Manages permission request handler registration and execution.
 *
 * @internal
 */
trait HasPermissionHandler
{
    /**
     * Permission handler.
     *
     * @var Closure(array, array): array|null
     */
    protected ?Closure $permissionHandler = null;

    /**
     * Register a permission handler.
     *
     * @param  Closure(array, array): array|null  $handler
     *
     * @internal
     */
    public function registerPermissionHandler(?Closure $handler): void
    {
        $this->permissionHandler = $handler;
    }

    /**
     * Handle a permission request.
     *
     * @internal
     */
    public function handlePermissionRequest(array $request): array
    {
        if ($this->permissionHandler === null) {
            return PermissionDecision::userNotAvailable();
        }

        try {
            $handlerResult = ($this->permissionHandler)($request, [
                'sessionId' => $this->sessionId,
                'managedSettingsEnabled' => $this->managedSettingsEnabled,
            ]);

            return $this->extractDecisionResult($handlerResult);
        } catch (Throwable $e) {
            report($e);

            return PermissionDecision::userNotAvailable();
        }
    }

    /**
     * Execute the permission handler and send the result back via RPC.
     * Runs in a new Fiber to allow async RPC calls without blocking the event loop.
     *
     * @internal
     */
    protected function executePermissionAndRespond(string $requestId, array $permissionRequest): void
    {
        $fiber = new \Fiber(function () use ($requestId, $permissionRequest): void {
            try {
                $handlerResult = ($this->permissionHandler)($permissionRequest, [
                    'sessionId' => $this->sessionId,
                    'managedSettingsEnabled' => $this->managedSettingsEnabled,
                ]);

                $result = $this->extractDecisionResult($handlerResult);
                $decisionContext = $this->extractDecisionContext($handlerResult);

                if (($result['kind'] ?? null) === PermissionDecision::NO_RESULT) {
                    return;
                }

                $this->rpc()->permissions()->handlePendingPermissionRequest(
                    new PermissionDecisionRequest(
                        requestId: $requestId,
                        result: $result,
                        decisionContext: $decisionContext,
                    )
                );
            } catch (Throwable $e) {
                report($e);

                try {
                    $this->rpc()->permissions()->handlePendingPermissionRequest(
                        new PermissionDecisionRequest(
                            requestId: $requestId,
                            result: PermissionDecision::userNotAvailable(),
                        )
                    );
                } catch (Throwable) {
                    // Connection lost or RPC error — nothing we can do
                }
            }
        });

        $fiber->start();
    }

    /**
     * Extract the plain decision result array from a handler return value,
     * unwrapping an attributed result (`['kind' => 'attributed', 'result' => ..., 'decisionContext' => ...]`) if present.
     *
     * @internal
     */
    protected function extractDecisionResult(array $handlerResult): array
    {
        if (($handlerResult['kind'] ?? null) === 'attributed') {
            return $handlerResult['result'] ?? [];
        }

        return $handlerResult;
    }

    /**
     * Extract the optional {@see PermissionDecisionContext} from an attributed handler return value.
     *
     * @internal
     */
    protected function extractDecisionContext(array $handlerResult): ?PermissionDecisionContext
    {
        if (($handlerResult['kind'] ?? null) === 'attributed' && isset($handlerResult['decisionContext'])) {
            return PermissionDecisionContext::fromArray($handlerResult['decisionContext']);
        }

        return null;
    }
}
