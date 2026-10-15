<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Client;

use Revolution\Copilot\Support\CopilotRequestAdapter;

/**
 * Registers the experimental LLM inference reverse-RPC handlers.
 *
 * @internal
 */
trait HandlesRequestHandler
{
    protected ?CopilotRequestAdapter $requestHandlerAdapter = null;

    protected function registerRequestHandler(): void
    {
        if ($this->requestHandler === null) {
            return;
        }

        $this->requestHandlerAdapter = new CopilotRequestAdapter(
            $this->requestHandler,
            fn () => $this->rpc()->llmInference(),
        );

        $this->rpcClient->setRequestHandler(
            'llmInference.httpRequestStart',
            fn (array $params) => $this->requestHandlerAdapter->handleStart($params),
        );
        $this->rpcClient->setRequestHandler(
            'llmInference.httpRequestChunk',
            fn (array $params) => $this->requestHandlerAdapter->handleChunk($params),
        );
    }

    protected function cancelPendingRequestHandlerExchanges(): void
    {
        $this->requestHandlerAdapter?->cancelPending();
    }
}
