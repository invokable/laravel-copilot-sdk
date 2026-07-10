<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A single user message to append to the session as part of a `session.sendMessages` turn.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SendMessageItem implements Arrayable
{
    /**
     * @param  string  $prompt  The user message text
     * @param  string|null  $displayPrompt  If provided, this is shown in the timeline instead of `prompt`
     * @param  array|null  $attachments  Optional attachments to include with this message
     * @param  string|null  $requiredTool  If set, the request will fail if the named tool is not available
     */
    public function __construct(
        public string $prompt,
        public ?string $displayPrompt = null,
        public ?array $attachments = null,
        public ?string $requiredTool = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            prompt: Arr::string($data, 'prompt'),
            displayPrompt: isset($data['displayPrompt']) ? Arr::string($data, 'displayPrompt') : null,
            attachments: $data['attachments'] ?? null,
            requiredTool: isset($data['requiredTool']) ? Arr::string($data, 'requiredTool') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'prompt' => $this->prompt,
            'displayPrompt' => $this->displayPrompt,
            'attachments' => $this->attachments,
            'requiredTool' => $this->requiredTool,
        ], fn ($v) => $v !== null);
    }
}
