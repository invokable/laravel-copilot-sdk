<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\QueuePendingItemsKind;
use Revolution\Copilot\Enums\SendAgentMode;

/**
 * A single pending queued item.
 */
readonly class QueuePendingItems implements Arrayable
{
    /**
     * @param  string  $id  Stable identifier for this queue entry
     * @param  string  $displayText  Human-readable text to display for this queue entry in the UI
     * @param  QueuePendingItemsKind  $kind  Whether this item is a queued user message or a queued slash command / model change
     * @param  SendAgentMode|string  $agentMode  Agent mode the queued item will run in
     */
    public function __construct(
        public string $id,
        public string $displayText,
        public QueuePendingItemsKind $kind,
        public SendAgentMode|string $agentMode,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id', ''),
            displayText: Arr::string($data, 'displayText', ''),
            kind: QueuePendingItemsKind::from($data['kind'] ?? QueuePendingItemsKind::Message->value),
            agentMode: isset($data['agentMode'])
                ? ($data['agentMode'] instanceof SendAgentMode ? $data['agentMode'] : (SendAgentMode::tryFrom($data['agentMode']) ?? $data['agentMode']))
                : SendAgentMode::INTERACTIVE,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'displayText' => $this->displayText,
            'kind' => $this->kind->value,
            'agentMode' => $this->agentMode instanceof SendAgentMode ? $this->agentMode->value : $this->agentMode,
        ];
    }
}
