<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for handling a pending tool call.
 *
 * Provide either $result or $error, but not both:
 * - $result: a plain string, or a structured {@see ToolCallResult} with textResultForLlm and
 *   optionally resultType and toolTelemetry
 * - $error: a string describing the error that occurred during tool execution
 */
readonly class ToolsHandlePendingToolCallRequest implements Arrayable
{
    /**
     * @param  string  $requestId  The ID of the pending tool call to handle
     * @param  string|ToolCallResult|array|null  $result  Plain string result or structured result object
     * @param  ?string  $error  Error message if tool execution failed
     */
    public function __construct(
        public string $requestId,
        public string|ToolCallResult|array|null $result = null,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: $data['requestId'],
            result: $data['result'] ?? null,
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = ['requestId' => $this->requestId];

        if ($this->result !== null) {
            $arr['result'] = $this->result instanceof ToolCallResult
                ? $this->result->toArray()
                : $this->result;
        }

        if ($this->error !== null) {
            $arr['error'] = $this->error;
        }

        return $arr;
    }
}
