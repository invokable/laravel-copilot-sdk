<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for executing a tool offered by a session.
 *
 * @experimental
 */
readonly class ToolsExecuteRequest implements Arrayable
{
    /**
     * @param  mixed  $arguments  Any JSON-compatible tool arguments
     */
    public function __construct(
        public string $name,
        public mixed $arguments,
        public ?string $toolCallId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            arguments: $data['arguments'] ?? null,
            toolCallId: $data['toolCallId'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            // PHP encodes an empty array as [], but the RPC schema expects a JSON object here.
            'arguments' => $this->arguments === [] ? (object) [] : $this->arguments,
        ];

        if ($this->toolCallId !== null) {
            $data['toolCallId'] = $this->toolCallId;
        }

        return $data;
    }
}
