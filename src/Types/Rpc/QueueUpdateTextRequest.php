<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for updating the text of a queued item.
 */
readonly class QueueUpdateTextRequest implements Arrayable
{
    /**
     * @param  string  $id  Identifier of the queued item to update.
     * @param  string  $prompt  Updated prompt text.
     * @param  ?string  $displayPrompt  Updated user-facing display text.
     */
    public function __construct(
        public string $id,
        public string $prompt,
        public ?string $displayPrompt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id'),
            prompt: Arr::string($data, 'prompt'),
            displayPrompt: isset($data['displayPrompt']) ? Arr::string($data, 'displayPrompt') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'prompt' => $this->prompt,
            'displayPrompt' => $this->displayPrompt,
        ], fn ($value) => $value !== null);
    }
}
