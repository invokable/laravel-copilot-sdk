<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AgentMode;

/**
 * Serializable message fields accepted by queue.insertAt.
 */
readonly class QueueInsertMessage implements Arrayable
{
    /**
     * @param  string  $prompt  The user message text.
     * @param  ?string  $displayPrompt  Optional user-facing display text.
     * @param  ?array  $attachments  Optional attachments for the message.
     * @param  ?AgentMode|string  $agentMode  Optional explicit agent mode.
     * @param  ?string  $source  Optional provenance source.
     * @param  ?bool  $billable  Whether the message is billable.
     * @param  ?string  $requiredTool  Required tool name for the turn, when any.
     * @param  ?array<string, string>  $requestHeaders  Per-turn request headers.
     * @param  ?string  $mode  Optional send mode.
     * @param  ?bool  $prepend  Whether to prepend the item.
     * @param  ?bool  $wait  Whether to wait before sending.
     */
    public function __construct(
        public string $prompt,
        public ?string $displayPrompt = null,
        public ?array $attachments = null,
        public AgentMode|string|null $agentMode = null,
        public ?string $source = null,
        public ?bool $billable = null,
        public ?string $requiredTool = null,
        public ?array $requestHeaders = null,
        public ?string $mode = null,
        public ?bool $prepend = null,
        public ?bool $wait = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $agentMode = $data['agentMode'] ?? null;

        if (is_string($agentMode)) {
            $agentMode = AgentMode::tryFrom($agentMode) ?? $agentMode;
        }

        return new self(
            prompt: Arr::string($data, 'prompt'),
            displayPrompt: isset($data['displayPrompt']) ? Arr::string($data, 'displayPrompt') : null,
            attachments: $data['attachments'] ?? null,
            agentMode: $agentMode,
            source: isset($data['source']) ? Arr::string($data, 'source') : null,
            billable: $data['billable'] ?? null,
            requiredTool: isset($data['requiredTool']) ? Arr::string($data, 'requiredTool') : null,
            requestHeaders: $data['requestHeaders'] ?? null,
            mode: isset($data['mode']) ? Arr::string($data, 'mode') : null,
            prepend: $data['prepend'] ?? null,
            wait: $data['wait'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'prompt' => $this->prompt,
            'displayPrompt' => $this->displayPrompt,
            'attachments' => $this->attachments,
            'agentMode' => $this->agentMode instanceof AgentMode ? $this->agentMode->value : $this->agentMode,
            'source' => $this->source,
            'billable' => $this->billable,
            'requiredTool' => $this->requiredTool,
            'requestHeaders' => $this->requestHeaders,
            'mode' => $this->mode,
            'prepend' => $this->prepend,
            'wait' => $this->wait,
        ], fn ($value) => $value !== null);
    }
}
