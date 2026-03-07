<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for handling a pending tool call.
 *
 * The result can be either:
 * - A plain string result
 * - An array with textResultForLlm, optionally resultType, error, toolTelemetry
 * - null (when providing error instead)
 */
readonly class SessionToolsHandlePendingToolCallParams implements Arrayable
{
    public function __construct(
        public string $requestId,
        /** Plain string result or structured result object */
        public string|array|null $result = null,
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
            $arr['result'] = $this->result;
        }

        if ($this->error !== null) {
            $arr['error'] = $this->error;
        }

        return $arr;
    }
}
