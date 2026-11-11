<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Illuminate\Support\Facades\Log;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\Rpc\ShellAuthenticationRequest;
use Revolution\Copilot\Types\Rpc\ShellAuthenticationResponse;
use Throwable;

/**
 * Manages shell authentication request handler registration and execution.
 *
 * @internal
 */
trait HasShellAuthenticationHandler
{
    /**
     * @var Closure(ShellAuthenticationRequest, array{sessionId: string, signal: CancellationToken}): ShellAuthenticationResponse|array|null
     */
    protected ?Closure $shellAuthenticationHandler = null;

    /**
     * @var array<string, CancellationToken>
     */
    protected array $pendingShellAuthenticationRequests = [];

    /**
     * @param  Closure(ShellAuthenticationRequest, array{sessionId: string, signal: CancellationToken}): ShellAuthenticationResponse|array|null  $handler
     *
     * @internal
     */
    public function registerShellAuthenticationHandler(?Closure $handler): void
    {
        $this->shellAuthenticationHandler = $handler;
    }

    /**
     * @internal
     */
    public function handleShellAuthenticationRequest(
        ShellAuthenticationRequest $request,
        CancellationToken $requestToken,
    ): ShellAuthenticationResponse {
        if (
            $this->shellAuthenticationHandler === null
            || $requestToken->isCancellationRequested()
            || isset($this->pendingShellAuthenticationRequests[$request->requestId])
        ) {
            return new ShellAuthenticationResponse(action: 'cancel');
        }

        $signal = new CancellationToken;
        $removeCancellationListener = $requestToken->onCancellationRequested(
            fn () => $signal->cancel(),
        );
        $this->pendingShellAuthenticationRequests[$request->requestId] = $signal;

        try {
            $result = ($this->shellAuthenticationHandler)($request, [
                'sessionId' => $this->sessionId,
                'signal' => $signal,
            ]);

            if ($result instanceof ShellAuthenticationResponse) {
                $response = $result;
            } elseif (is_array($result)) {
                $response = ShellAuthenticationResponse::fromArray($result);
            } else {
                return new ShellAuthenticationResponse(action: 'cancel');
            }

            $responseData = $response->toArray();
            if (
                $signal->isCancellationRequested()
                || $response->action !== 'accept'
                || ! is_string($responseData['value'] ?? null)
            ) {
                return new ShellAuthenticationResponse(action: 'cancel');
            }

            return $response;
        } catch (Throwable $exception) {
            if (! $signal->isCancellationRequested()) {
                Log::warning('Shell authentication handler failed', [
                    'sessionId' => $this->sessionId,
                    'requestId' => $request->requestId,
                    'exceptionType' => get_debug_type($exception),
                ]);
            }

            return new ShellAuthenticationResponse(action: 'cancel');
        } finally {
            $removeCancellationListener();
            unset($this->pendingShellAuthenticationRequests[$request->requestId]);
        }
    }

    /**
     * @internal
     */
    public function cancelShellAuthenticationRequest(string $requestId): void
    {
        ($this->pendingShellAuthenticationRequests[$requestId] ?? null)?->cancel();
    }

    /**
     * @internal
     */
    public function cancelPendingShellAuthenticationRequests(): void
    {
        foreach ($this->pendingShellAuthenticationRequests as $signal) {
            $signal->cancel();
        }

        $this->pendingShellAuthenticationRequests = [];
    }
}
