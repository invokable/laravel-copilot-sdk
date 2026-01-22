<?php

declare(strict_types=1);

namespace Revolution\Copilot\JsonRpc;

/**
 * Represents a JSON-RPC 2.0 message.
 */
readonly class JsonRpcMessage
{
    public function __construct(
        public string|int|null $id = null,
        public ?string $method = null,
        public array $params = [],
        public mixed $result = null,
        public ?array $error = null,
    ) {}

    /**
     * Create a request message.
     */
    public static function request(string|int $id, string $method, array $params = []): self
    {
        return new self(id: $id, method: $method, params: $params);
    }

    /**
     * Create a notification message (no id).
     */
    public static function notification(string $method, array $params = []): self
    {
        return new self(method: $method, params: $params);
    }

    /**
     * Create a response message.
     */
    public static function response(string|int $id, mixed $result): self
    {
        return new self(id: $id, result: $result);
    }

    /**
     * Create an error response message.
     */
    public static function errorResponse(string|int $id, int $code, string $message, mixed $data = null): self
    {
        return new self(id: $id, error: [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Create from parsed JSON array.
     */
    public static function fromArray(array $data): self
    {
        // JSON-RPC 2.0 allows id to be string, number, or null
        // Preserve the original type for proper response matching
        $id = $data['id'] ?? null;

        return new self(
            id: $id,
            method: $data['method'] ?? null,
            params: $data['params'] ?? [],
            result: $data['result'] ?? null,
            error: $data['error'] ?? null,
        );
    }

    /**
     * Check if this is a request (has id and method).
     */
    public function isRequest(): bool
    {
        return $this->id !== null && $this->method !== null;
    }

    /**
     * Check if this is a notification (has method but no id).
     */
    public function isNotification(): bool
    {
        return $this->id === null && $this->method !== null;
    }

    /**
     * Check if this is a response (has id but no method).
     */
    public function isResponse(): bool
    {
        return $this->id !== null && $this->method === null;
    }

    /**
     * Check if this is an error response.
     */
    public function isError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Convert to JSON-RPC 2.0 format array.
     */
    public function toArray(): array
    {
        $data = ['jsonrpc' => '2.0'];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        if ($this->method !== null) {
            $data['method'] = $this->method;
            $data['params'] = $this->params;
        }

        if ($this->result !== null) {
            $data['result'] = $this->result;
        }

        if ($this->error !== null) {
            $data['error'] = $this->error;
        }

        return $data;
    }

    /**
     * Convert to JSON string.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Encode with Content-Length header for stdio transport.
     */
    public function encode(): string
    {
        $content = $this->toJson();
        $length = strlen($content);

        return "Content-Length: {$length}\r\n\r\n{$content}";
    }
}
