<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/** @experimental */
readonly class HistoryImportConversationEvent implements Arrayable
{
    public function __construct(
        public string $type,
        public HistoryImportUserMessage|HistoryImportAssistantMessage|HistoryImportToolInvocation|HistoryImportToolCompletion|array $data,
    ) {}

    public static function fromArray(array $data): self
    {
        $type = Arr::string($data, 'type');
        $payload = $data['data'];

        return new self(type: $type, data: self::payload($type, $payload));
    }

    private static function payload(string $type, HistoryImportUserMessage|HistoryImportAssistantMessage|HistoryImportToolInvocation|HistoryImportToolCompletion|array $data): HistoryImportUserMessage|HistoryImportAssistantMessage|HistoryImportToolInvocation|HistoryImportToolCompletion
    {
        $class = match ($type) {
            'user.message' => HistoryImportUserMessage::class,
            'assistant.message' => HistoryImportAssistantMessage::class,
            'tool.execution_start' => HistoryImportToolInvocation::class,
            'tool.execution_complete' => HistoryImportToolCompletion::class,
            default => throw new InvalidArgumentException("Unknown history import event type: {$type}"),
        };

        if (is_array($data)) {
            return $class::fromArray($data);
        }

        if (! $data instanceof $class) {
            throw new InvalidArgumentException("Invalid payload for history import event type: {$type}");
        }

        return $data;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'data' => self::payload($this->type, $this->data)->toArray(),
        ];
    }
}
