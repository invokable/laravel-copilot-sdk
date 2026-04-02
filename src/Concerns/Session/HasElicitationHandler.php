<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\Types\ElicitationContext;
use Revolution\Copilot\Types\Rpc\SessionUiHandlePendingElicitationParams;
use Throwable;

/**
 * Manages elicitation request handler registration and execution.
 *
 * When a registered handler exists, incoming `elicitation.requested` broadcast events
 * are forwarded to the handler. The handler's response is sent back to the CLI
 * via the `session.ui.handlePendingElicitation` RPC method.
 *
 * @internal
 */
trait HasElicitationHandler
{
    /**
     * Elicitation handler.
     *
     * @var Closure(ElicitationContext): mixed|null
     */
    protected ?Closure $elicitationHandler = null;

    /**
     * Register an elicitation handler.
     *
     * @param  Closure(ElicitationContext): mixed|null  $handler
     *
     * @internal
     */
    public function registerElicitationHandler(?Closure $handler): void
    {
        $this->elicitationHandler = $handler;
    }

    /**
     * Handle an elicitation.requested broadcast event.
     *
     * Invokes the registered handler and responds via handlePendingElicitation RPC.
     *
     * @internal
     */
    protected function handleElicitationRequest(ElicitationContext $context, string $requestId): void
    {
        if ($this->elicitationHandler === null) {
            return;
        }

        try {
            $result = ($this->elicitationHandler)($context);

            // Normalize result to array
            $resultArray = match (true) {
                $result instanceof Arrayable => $result->toArray(),
                is_array($result) => $result,
                default => ['action' => ElicitationAction::CANCEL->value],
            };

            $this->rpc()->ui()->handlePendingElicitation(
                new SessionUiHandlePendingElicitationParams(
                    requestId: $requestId,
                    result: $resultArray,
                ),
            );
        } catch (Throwable) {
            // Handler failed — attempt to cancel so the request doesn't hang
            try {
                $this->rpc()->ui()->handlePendingElicitation(
                    new SessionUiHandlePendingElicitationParams(
                        requestId: $requestId,
                        result: ['action' => ElicitationAction::CANCEL->value],
                    ),
                );
            } catch (Throwable) {
                // Connection lost or RPC error — nothing we can do
            }
        }
    }
}
