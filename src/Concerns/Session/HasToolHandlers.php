<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Revolution\Copilot\Support\TraceContext;
use Revolution\Copilot\Types\Rpc\SessionToolsHandlePendingToolCallParams;
use Revolution\Copilot\Types\ToolResultObject;
use Throwable;

/**
 * Manages external tool handler registration and execution.
 *
 * @internal
 */
trait HasToolHandlers
{
    /**
     * Tool handlers.
     *
     * @var array<string, Closure(array, array): mixed>
     */
    protected array $toolHandlers = [];

    /**
     * @param  array<array{name: string, handler: Closure}>  $tools
     *
     * @internal
     */
    public function registerTools(array $tools): void
    {
        $this->toolHandlers = [];

        foreach ($tools as $tool) {
            if (isset($tool['name'], $tool['handler'])) {
                $this->toolHandlers[$tool['name']] = $tool['handler'];
            }
        }
    }

    /**
     * Get a tool handler by name.
     *
     * @internal
     */
    public function getToolHandler(string $name): ?Closure
    {
        return $this->toolHandlers[$name] ?? null;
    }

    /**
     * Execute a tool handler and send the result back via RPC.
     * Runs in a new Fiber to allow async RPC calls without blocking the event loop.
     *
     * @internal
     */
    protected function executeToolAndRespond(string $requestId, string $toolName, ?string $toolCallId, mixed $arguments, Closure $handler, ?string $traceparent = null, ?string $tracestate = null): void
    {
        $fiber = new \Fiber(function () use ($requestId, $toolName, $toolCallId, $arguments, $handler, $traceparent, $tracestate): void {
            $scope = TraceContext::restore($traceparent, $tracestate);

            try {
                $invocation = [
                    'sessionId' => $this->sessionId,
                    'toolCallId' => $toolCallId,
                    'toolName' => $toolName,
                    'arguments' => $arguments,
                ];

                if ($traceparent !== null) {
                    $invocation['traceparent'] = $traceparent;
                }
                if ($tracestate !== null) {
                    $invocation['tracestate'] = $tracestate;
                }

                /** @var ToolResultObject|array|string|mixed $rawResult */
                $rawResult = $handler($arguments, $invocation);

                if ($rawResult === null) {
                    $result = '';
                } elseif ($rawResult instanceof ToolResultObject) {
                    $result = $rawResult->toArray();
                } elseif (is_string($rawResult) || is_array($rawResult)) {
                    $result = $rawResult;
                } else {
                    $result = (string) $rawResult;
                }

                // Send failure via error param for consistent server-side formatting
                if (is_array($result) && ($result['resultType'] ?? null) === 'failure' && isset($result['error'])) {
                    $this->rpc()->tools()->handlePendingToolCall(
                        new SessionToolsHandlePendingToolCallParams(
                            requestId: $requestId,
                            error: $result['error'],
                        )
                    );
                } else {
                    $this->rpc()->tools()->handlePendingToolCall(
                        new SessionToolsHandlePendingToolCallParams(
                            requestId: $requestId,
                            result: $result,
                        )
                    );
                }
            } catch (Throwable $e) {
                try {
                    $this->rpc()->tools()->handlePendingToolCall(
                        new SessionToolsHandlePendingToolCallParams(
                            requestId: $requestId,
                            error: $e->getMessage(),
                        )
                    );
                } catch (Throwable) {
                    // Connection lost or RPC error — nothing we can do
                }
            } finally {
                TraceContext::detach($scope);
            }
        });

        $fiber->start();
    }
}
