<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** External tool call that is still waiting for a result. */
readonly class PendingExternalToolRequest implements Arrayable
{
    public function __construct(
        public string $requestId,
        public string $toolCallId,
        public string $toolName,
        public mixed $arguments = null,
        public ?string $providerId = null,
        public ?string $agentId = null,
        private bool $argumentsProvided = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: $data['requestId'] ?? '',
            toolCallId: $data['toolCallId'] ?? '',
            toolName: $data['toolName'] ?? '',
            arguments: $data['arguments'] ?? null,
            providerId: $data['providerId'] ?? null,
            agentId: $data['agentId'] ?? null,
            argumentsProvided: array_key_exists('arguments', $data),
        );
    }

    public function toArray(): array
    {
        $data = array_filter([
            'requestId' => $this->requestId,
            'toolCallId' => $this->toolCallId,
            'toolName' => $this->toolName,
            'providerId' => $this->providerId,
            'agentId' => $this->agentId,
        ], fn ($value) => $value !== null);

        if ($this->argumentsProvided || $this->arguments !== null) {
            $data['arguments'] = $this->arguments;
        }

        return $data;
    }
}
