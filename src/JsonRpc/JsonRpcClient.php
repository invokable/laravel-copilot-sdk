<?php

declare(strict_types=1);

namespace Revolution\Copilot\JsonRpc;

use Closure;
use Illuminate\Support\Str;
use Revolution\Copilot\Contracts\Transport;
use Revolution\Copilot\Events\JsonRpc\MessageReceived;
use Revolution\Copilot\Events\JsonRpc\MessageSending;
use Revolution\Copilot\Events\JsonRpc\ResponseReceived;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Exceptions\StrayRequestException;
use Revolution\Copilot\Facades\Copilot;

/**
 * JSON-RPC 2.0 client for stdio transport.
 *
 * Handles bidirectional communication with Content-Length headers.
 */
class JsonRpcClient
{
    /**
     * Pending requests waiting for responses.
     *
     * @var array<string, array{resolve: Closure, reject: Closure}>
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
        $this->transport->start();
    }

    /**
     * Stop the client.
     */
    public function stop(): void
    {
        $this->running = false;
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
     * Process incoming messages (call this in a loop or after sending requests).
     */
    public function processMessages(float $timeout = 0.1): void
    {
        $endTime = microtime(true) + $timeout;

        while (microtime(true) < $endTime) {
            $message = $this->readMessage(0.01);

            if ($message === null) {
                usleep(1000); // 1ms

                continue;
            }

            $this->handleMessage($message);
        }
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
        $endTime = microtime(true) + $timeout;
        $message = null;
        $result = null;
        $error = null;
        $received = false;

        while (microtime(true) < $endTime && ! $received) {
            $message = $this->readMessage(0.1);

            if ($message === null) {
                continue;
            }

            if ($message->isResponse() && $message->id === $requestId) {
                if ($message->isError()) {
                    $error = $message->error;
                } else {
                    $result = $message->result;
                }
                $received = true;
            } else {
                // Handle other messages (notifications, other requests)
                $this->handleMessage($message);
            }
        }

        if (! $received) {
            throw new JsonRpcException(-32000, "Timeout waiting for response to request {$requestId}");
        }

        if ($error !== null) {
            throw new JsonRpcException(
                $error['code'] ?? -1,
                $error['message'] ?? 'Unknown error',
                $error['data'] ?? null,
            );
        }

        ResponseReceived::dispatch($requestId, $message);

        return $result;
    }

    /**
     * Read a single message from the stream.
     */
    protected function readMessage(float $timeout = 0.1): ?JsonRpcMessage
    {
        $content = $this->transport->read($timeout);

        $data = json_decode($content, true);

        if (! is_array($data)) {
            return null;
        }

        return JsonRpcMessage::fromArray($data);
    }

    /**
     * Handle an incoming message.
     */
    protected function handleMessage(JsonRpcMessage $message): void
    {
        MessageReceived::dispatch($message);

        if ($message->isNotification()) {
            $this->handleNotification($message);
        } elseif ($message->isRequest()) {
            $this->handleRequest($message);
        }
        // Responses are handled in waitForResponse
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
