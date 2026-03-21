<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Revolution\Copilot\Support\PermissionRequestResultKind;
use Revolution\Copilot\Types\Rpc\SessionPermissionsHandlePendingPermissionRequestParams;
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
            return PermissionRequestResultKind::deniedNoApprovalRuleAndCouldNotRequestFromUser();
        }

        try {
            return ($this->permissionHandler)($request, ['sessionId' => $this->sessionId]);
        } catch (Throwable) {
            return PermissionRequestResultKind::deniedNoApprovalRuleAndCouldNotRequestFromUser();
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
                $result = ($this->permissionHandler)($permissionRequest, ['sessionId' => $this->sessionId]);

                if (($result['kind'] ?? null) === PermissionRequestResultKind::NO_RESULT) {
                    return;
                }

                $this->rpc()->permissions()->handlePendingPermissionRequest(
                    new SessionPermissionsHandlePendingPermissionRequestParams(
                        requestId: $requestId,
                        result: $result,
                    )
                );
            } catch (Throwable) {
                try {
                    $this->rpc()->permissions()->handlePendingPermissionRequest(
                        new SessionPermissionsHandlePendingPermissionRequestParams(
                            requestId: $requestId,
                            result: PermissionRequestResultKind::deniedNoApprovalRuleAndCouldNotRequestFromUser(),
                        )
                    );
                } catch (Throwable) {
                    // Connection lost or RPC error — nothing we can do
                }
            }
        });

        $fiber->start();
    }
}
