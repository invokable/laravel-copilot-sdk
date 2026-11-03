<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\SessionManagementCreateHostRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementCreateResult;
use RuntimeException;
use Throwable;

/**
 * Manages the host callback for session-management create requests.
 *
 * @internal
 */
trait HasSessionCreationHandler
{
    /**
     * @var Closure(SessionManagementCreateHostRequest): SessionManagementCreateResult|array
     */
    protected ?Closure $sessionCreationHandler = null;

    /**
     * @param  Closure(SessionManagementCreateHostRequest): SessionManagementCreateResult|array  $handler
     *
     * @internal
     */
    public function registerSessionCreationHandler(?Closure $handler): void
    {
        $this->sessionCreationHandler = $handler;
    }

    /**
     * Handle a sessionManagement.create reverse-RPC request.
     *
     * @internal
     */
    public function handleSessionManagementCreate(SessionManagementCreateHostRequest $request): SessionManagementCreateResult
    {
        if ($this->sessionCreationHandler === null
            || $request->sessionId !== $this->sessionId
            || $request->parentSessionId !== $this->sessionId) {
            throw new RuntimeException('Session creation handler is unavailable');
        }

        try {
            $result = ($this->sessionCreationHandler)($request);

            if ($result instanceof SessionManagementCreateResult) {
                return $result;
            }

            if ($result instanceof Arrayable) {
                return SessionManagementCreateResult::fromArray($result->toArray());
            }

            if (is_array($result)) {
                return SessionManagementCreateResult::fromArray($result);
            }

            throw new RuntimeException('Session creation callback returned an invalid result');
        } catch (Throwable $exception) {
            throw new RuntimeException('Session creation callback failed', previous: $exception);
        }
    }
}
