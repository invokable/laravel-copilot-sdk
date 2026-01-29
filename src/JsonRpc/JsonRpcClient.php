<?php

declare(strict_types=1);

namespace Revolution\Copilot\JsonRpc;

use Closure;
use Illuminate\Support\Str;
use Revolt\EventLoop;
use Revolution\Copilot\Contracts\Transport;
use Revolution\Copilot\Events\JsonRpc\MessageReceived;
use Revolution\Copilot\Events\JsonRpc\MessageSending;
use Revolution\Copilot\Events\JsonRpc\ResponseReceived;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Exceptions\StrayRequestException;
use Revolution\Copilot\Facades\Copilot;

/**
 * JSON-RPC 2.0 client.
 *
 * Handles bidirectional communication with Content-Length headers.
 */
class JsonRpcClient
{
    /**
     * Pending requests waiting for responses.
     *
     * @var array<string, array{suspension: \Revolt\EventLoop\Suspension, result: mixed, error: array|null}>
     */
    protected array $pendingRequests = [];

    /**
     * Notification handler callback.
     *
     * @var Closure(string, array): void|null
     */
    protected ?Closure $notificationHandler = null;

    /**
     * Request handlers for incoming requests from server.
     *
     * @var array<string, Closure(array): mixed>
     */
    protected array $requestHandlers = [];

    /**
     * Whether the client is running.
     */
    protected bool $running = false;

    public function __construct(
        protected Transport $transport,
    ) {
        //
    }

    /**
     * Start the client.
     */
    public function start(): void
    {
        $this->running = true;

        $this->transport->onReceive($this->handleReceived(...));
        $this->transport->start();
    }

    /**
     * Stop the client.
     */
    public function stop(): void
    {
        $this->running = false;
        $this->transport->stop();
        $this->pendingRequests = [];
    }

    /**
     * Check if the client is running.
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Send a JSON-RPC request and wait for response.
     *
     * @throws JsonRpcException
     */
    public function request(string $method, array $params = [], float $timeout = 30.0): mixed
    {
        if (! Copilot::isAllowedMethod($method)) {
            throw new StrayRequestException($method);
        }

        $requestId = Str::uuid()->toString();
        $message = JsonRpcMessage::request($requestId, $method, $params);

        $this->sendMessage($message);

        return $this->waitForResponse($requestId, $timeout);
    }

    /**
     * Send a JSON-RPC notification (no response expected).
     */
    public function notify(string $method, array $params = []): void
    {
        $message = JsonRpcMessage::notification($method, $params);
        $this->sendMessage($message);
    }

    /**
     * Set handler for incoming notifications from server.
     *
     * @param  Closure(string $method, array $params): void  $handler
     */
    public function setNotificationHandler(Closure $handler): void
    {
        $this->notificationHandler = $handler;
    }

    /**
     * Set handler for incoming requests from server.
     *
     * @param  Closure(array $params): mixed  $handler
     */
    public function setRequestHandler(string $method, Closure $handler): void
    {
        $this->requestHandlers[$method] = $handler;
    }

    /**
     * Remove a request handler.
     */
    public function removeRequestHandler(string $method): void
    {
        unset($this->requestHandlers[$method]);
    }

    /**
     * Handle received data from transport.
     */
    protected function handleReceived(string $content): void
    {
        $data = json_decode($content, true);

        if (! is_array($data)) {
            return;
        }

        $message = JsonRpcMessage::fromArray($data);
        $this->handleMessage($message);
    }

    /**
     * Send a message to the server.
     */
    protected function sendMessage(JsonRpcMessage $message): void
    {
        MessageSending::dispatch($message);

        $encoded = $message->encode();

        $this->transport->send($encoded);
    }

    /**
     * Send a response to an incoming request.
     */
    protected function sendResponse(string|int $id, mixed $result): void
    {
        $message = JsonRpcMessage::response($id, $result);
        $this->sendMessage($message);
    }

    /**
     * Send an error response to an incoming request.
     */
    protected function sendErrorResponse(string|int $id, int $code, string $errorMessage, mixed $data = null): void
    {
        $message = JsonRpcMessage::errorResponse($id, $code, $errorMessage, $data);
        $this->sendMessage($message);
    }

    /**
     * Wait for a specific response.
     *
     * @throws JsonRpcException
     */
    protected function waitForResponse(string $requestId, float $timeout): mixed
    {
        $suspension = EventLoop::getSuspension();

        $this->pendingRequests[$requestId] = [
            'suspension' => $suspension,
            'result' => null,
            'error' => null,
        ];

        $timeoutError = false;
        $timeoutId = EventLoop::delay($timeout, function () use ($requestId, &$timeoutError): void {
            $timeoutError = true;
            $this->resumePendingRequest($requestId);
        });

        $suspension->suspend();

        EventLoop::cancel($timeoutId);

        $pending = $this->pendingRequests[$requestId] ?? null;
        unset($this->pendingRequests[$requestId]);

        if ($timeoutError) {
            throw new JsonRpcException(-32000, "Timeout waiting for response to request {$requestId}");
        }

        if ($pending !== null && $pending['error'] !== null) {
            $error = $pending['error'];
            throw new JsonRpcException(
                $error['code'] ?? -1,
                $error['message'] ?? 'Unknown error',
                $error['data'] ?? null,
            );
        }

        return $pending['result'] ?? null;
    }

    /**
     * Resume a pending request's suspension.
     */
    protected function resumePendingRequest(string $requestId): void
    {
        if (isset($this->pendingRequests[$requestId])) {
            $this->pendingRequests[$requestId]['suspension']->resume();
        }
    }

    /**
     * Handle an incoming message.
     */
    protected function handleMessage(JsonRpcMessage $message): void
    {
        MessageReceived::dispatch($message);

        if ($message->isResponse()) {
            $this->handleResponse($message);
        } elseif ($message->isNotification()) {
            $this->handleNotification($message);
        } elseif ($message->isRequest()) {
            $this->handleRequest($message);
        }
    }

    /**
     * Handle an incoming response.
     */
    protected function handleResponse(JsonRpcMessage $message): void
    {
        $requestId = $message->id;

        if (! isset($this->pendingRequests[$requestId])) {
            return;
        }

        ResponseReceived::dispatch($requestId, $message);

        if ($message->isError()) {
            $this->pendingRequests[$requestId]['error'] = $message->error;
        } else {
            $this->pendingRequests[$requestId]['result'] = $message->result;
        }

        $this->resumePendingRequest($requestId);
    }

    /**
     * Handle an incoming notification.
     */
    protected function handleNotification(JsonRpcMessage $message): void
    {
        if ($this->notificationHandler !== null) {
            ($this->notificationHandler)($message->method, $message->params);
        }
    }

    /**
     * Handle an incoming request from the server.
     */
    protected function handleRequest(JsonRpcMessage $message): void
    {
        $handler = $this->requestHandlers[$message->method] ?? null;

        if ($handler === null) {
            $this->sendErrorResponse(
                $message->id,
                -32601,
                "Method not found: {$message->method}",
            );

            return;
        }

        try {
            $result = $handler($message->params);
            $this->sendResponse($message->id, $result ?? []);
        } catch (JsonRpcException $e) {
            $this->sendErrorResponse($message->id, $e->code, $e->getMessage(), $e->data);
        } catch (\Throwable $e) {
            $this->sendErrorResponse($message->id, -32603, $e->getMessage());
        }
    }
}
