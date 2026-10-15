<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

use Closure;
use Fiber;
use GuzzleHttp\Psr7\Stream as GuzzleStream;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;
use Revolt\EventLoop;
use Revolution\Copilot\Rpc\PendingServerLlmInference;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPRequestChunkRequest;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPRequestStartRequest;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseChunkError;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseChunkRequest;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseStartRequest;
use RuntimeException;
use Throwable;

/**
 * Bridges LLM inference request frames to a request handler.
 *
 * @internal
 */
final class CopilotRequestAdapter
{
    /**
     * @var array<string, CopilotRequestExchange>
     */
    private array $pending = [];

    /**
     * @param  CopilotRequestHandler|Closure(CopilotRequestContext, string, string): Response  $handler
     * @param  Closure(): PendingServerLlmInference  $llmInference
     */
    public function __construct(
        private readonly CopilotRequestHandler|Closure $handler,
        private readonly Closure $llmInference,
    ) {}

    /**
     * @param  array<string, mixed>  $params
     * @return array{}
     */
    public function handleStart(array $params): array
    {
        $request = LlmInferenceHTTPRequestStartRequest::fromArray($params);
        $exchange = $this->getOrCreate($request->requestId);

        if (! $exchange->contextReady) {
            $exchange->setContext($request);
        }

        $this->schedule($exchange);

        return [];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{}
     */
    public function handleChunk(array $params): array
    {
        $request = LlmInferenceHTTPRequestChunkRequest::fromArray($params);
        $exchange = $this->getOrCreate($request->requestId);

        try {
            $exchange->pushChunk($request);
        } catch (Throwable $e) {
            $exchange->failure = $e;
        }

        $this->schedule($exchange);

        return [];
    }

    /**
     * Cancel pending exchanges when the JSON-RPC connection closes.
     */
    public function cancelPending(): void
    {
        foreach ($this->pending as $exchange) {
            $exchange->finished = true;
            $exchange->context?->signal->cancel();
        }

        $this->pending = [];
    }

    private function getOrCreate(string $requestId): CopilotRequestExchange
    {
        return $this->pending[$requestId] ??= new CopilotRequestExchange($requestId);
    }

    private function schedule(CopilotRequestExchange $exchange): void
    {
        if (! $exchange->contextReady
            || $exchange->scheduled
            || $exchange->finished
            || (! $exchange->bodyEnded && ! $exchange->cancelled && $exchange->failure === null)) {
            return;
        }

        $exchange->scheduled = true;

        EventLoop::queue(function () use ($exchange): void {
            if ($exchange->finished) {
                $this->forget($exchange);

                return;
            }

            $exchange->fiber = new Fiber(function () use ($exchange): void {
                try {
                    if ($exchange->cancelled) {
                        $this->fail($exchange, 499, 'Request cancelled by runtime', 'cancelled');

                        return;
                    }

                    if ($exchange->failure !== null) {
                        throw $exchange->failure;
                    }

                    if ($exchange->context->transport === 'websocket') {
                        $this->fail(
                            $exchange,
                            501,
                            'WebSocket transport is not supported by the Laravel request handler.',
                            'unsupported_transport',
                        );

                        return;
                    }

                    $this->forward($exchange);
                } catch (Throwable $e) {
                    if ($exchange->cancelled || $exchange->context->signal->isCancellationRequested()) {
                        $this->fail($exchange, 499, 'Request cancelled by runtime', 'cancelled');
                    } else {
                        $this->fail($exchange, 502, $e->getMessage(), 'request_handler_error');
                    }
                } finally {
                    $this->forget($exchange);
                }
            });
            $exchange->fiber->start();
        });
    }

    private function forward(CopilotRequestExchange $exchange): void
    {
        $context = $exchange->context;
        $response = $this->handler instanceof CopilotRequestHandler
            ? $this->handler->sendRequest($context, $exchange->method, $exchange->body)
            : ($this->handler)($context, $exchange->method, $exchange->body);

        if (! $response instanceof Response) {
            throw new InvalidArgumentException('requestHandler must return an Illuminate HTTP client response.');
        }

        if ($context->signal->isCancellationRequested()) {
            throw new RuntimeException('Request cancelled by runtime');
        }

        $psrResponse = $response->toPsrResponse();
        $headers = $psrResponse->getHeaders();
        $this->llmInference()->httpResponseStart(new LlmInferenceHTTPResponseStartRequest(
            headers: $headers,
            requestId: $exchange->requestId,
            status: $psrResponse->getStatusCode(),
            statusText: $psrResponse->getReasonPhrase() ?: null,
        ));
        $exchange->responseStarted = true;

        $body = $psrResponse->getBody();
        $bodyResource = $body instanceof GuzzleStream ? $body->detach() : null;

        try {
            if (is_resource($bodyResource)) {
                $nonBlocking = stream_set_blocking($bodyResource, false);

                while (! feof($bodyResource)) {
                    $chunk = $nonBlocking
                        ? $this->readResponseChunk($bodyResource, $context)
                        : fread($bodyResource, 32768);

                    if ($chunk === false) {
                        throw new RuntimeException('Unable to read the upstream response stream.');
                    }

                    $this->sendResponseBodyChunk($chunk, $exchange);
                }
            } else {
                while (! $body->eof()) {
                    if ($context->signal->isCancellationRequested()) {
                        throw new RuntimeException('Request cancelled by runtime');
                    }

                    $this->sendResponseBodyChunk($body->read(32768), $exchange);
                }
            }
        } finally {
            if (is_resource($bodyResource)) {
                fclose($bodyResource);
            }
            $body->close();
        }

        if ($context->signal->isCancellationRequested()) {
            throw new RuntimeException('Request cancelled by runtime');
        }

        $this->llmInference()->httpResponseChunk(new LlmInferenceHTTPResponseChunkRequest(
            data: '',
            requestId: $exchange->requestId,
            end: true,
        ));
        $exchange->finished = true;
    }

    private function sendResponseBodyChunk(string $chunk, CopilotRequestExchange $exchange): void
    {
        if ($chunk === '') {
            return;
        }

        $isText = preg_match('//u', $chunk) === 1;
        $this->llmInference()->httpResponseChunk(new LlmInferenceHTTPResponseChunkRequest(
            data: $isText ? $chunk : base64_encode($chunk),
            requestId: $exchange->requestId,
            binary: $isText ? null : true,
        ));
    }

    /**
     * Read a response chunk without blocking the event loop between streamed frames.
     *
     * @param  resource  $resource
     */
    private function readResponseChunk($resource, CopilotRequestContext $context): string
    {
        while (true) {
            if ($context->signal->isCancellationRequested()) {
                throw new RuntimeException('Request cancelled by runtime');
            }

            $chunk = fread($resource, 32768);

            if ($chunk === false) {
                throw new RuntimeException('Unable to read the upstream response stream.');
            }

            if ($chunk !== '' || feof($resource)) {
                return $chunk;
            }

            $suspension = EventLoop::getSuspension();
            $waiting = true;
            $resumeQueued = false;
            $wake = function () use (&$waiting, &$resumeQueued, $suspension): void {
                if (! $waiting || $resumeQueued) {
                    return;
                }

                $resumeQueued = true;
                EventLoop::queue(function () use (&$waiting, &$resumeQueued, $suspension): void {
                    $resumeQueued = false;

                    if ($waiting) {
                        $waiting = false;
                        $suspension->resume();
                    }
                });
            };
            $watcher = EventLoop::onReadable($resource, $wake);
            $removeCancellationListener = $context->signal->onCancellationRequested($wake);

            if (! $context->signal->isCancellationRequested()) {
                $suspension->suspend();
            } else {
                $waiting = false;
            }

            EventLoop::cancel($watcher);
            $removeCancellationListener();
        }
    }

    private function fail(CopilotRequestExchange $exchange, int $status, string $message, string $code): void
    {
        if ($exchange->finished) {
            return;
        }

        $exchange->finished = true;

        try {
            $llmInference = $this->llmInference();

            if (! $exchange->responseStarted) {
                $llmInference->httpResponseStart(new LlmInferenceHTTPResponseStartRequest(
                    headers: [],
                    requestId: $exchange->requestId,
                    status: $status,
                ));
                $exchange->responseStarted = true;
            }

            $llmInference->httpResponseChunk(new LlmInferenceHTTPResponseChunkRequest(
                data: '',
                requestId: $exchange->requestId,
                end: true,
                error: new LlmInferenceHTTPResponseChunkError(message: $message, code: $code),
            ));
        } catch (Throwable) {
            // The connection may already be closed.
        }
    }

    private function llmInference(): PendingServerLlmInference
    {
        return ($this->llmInference)();
    }

    private function forget(CopilotRequestExchange $exchange): void
    {
        if (($this->pending[$exchange->requestId] ?? null) === $exchange) {
            unset($this->pending[$exchange->requestId]);
        }

        $exchange->fiber = null;
    }
}

/**
 * State for one in-flight request, including body chunks that race the start frame.
 *
 * @internal
 */
final class CopilotRequestExchange
{
    public ?CopilotRequestContext $context = null;

    public string $method = 'GET';

    public string $body = '';

    public bool $contextReady = false;

    public bool $bodyEnded = false;

    public bool $cancelled = false;

    public bool $scheduled = false;

    public bool $responseStarted = false;

    public bool $finished = false;

    public ?Throwable $failure = null;

    public ?Fiber $fiber = null;

    public function __construct(
        public readonly string $requestId,
    ) {}

    public function setContext(LlmInferenceHTTPRequestStartRequest $request): void
    {
        $this->method = $request->method;
        $transport = $request->transport;
        $this->context = new CopilotRequestContext(
            requestId: $request->requestId,
            sessionId: $request->sessionId,
            agentId: $request->agentId,
            agentInvocationId: $request->agentInvocationId,
            parentAgentId: $request->parentAgentId,
            interactionType: $request->interactionType,
            transport: $transport instanceof \BackedEnum ? $transport->value : ($transport ?? 'http'),
            url: $request->url,
            headers: $request->headers,
            signal: new CancellationToken,
        );
        $this->contextReady = true;

        if ($this->cancelled) {
            $this->context->signal->cancel();
        }
    }

    public function pushChunk(LlmInferenceHTTPRequestChunkRequest $request): void
    {
        if ($this->cancelled || $this->finished) {
            return;
        }

        if ($request->cancel === true) {
            $this->cancelled = true;
            $this->context?->signal->cancel();

            return;
        }

        if ($request->data !== '') {
            $data = $request->binary === true
                ? base64_decode($request->data, true)
                : $request->data;

            if ($data === false) {
                throw new InvalidArgumentException('Request body chunk contains invalid base64 data.');
            }

            $this->body .= $data;
        }

        if ($request->end === true) {
            $this->bodyEnded = true;
        }
    }
}
