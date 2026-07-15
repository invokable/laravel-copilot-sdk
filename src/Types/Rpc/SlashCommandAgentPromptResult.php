<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Slash-command invocation result that submits an agent prompt, with display prompt,
 * optional mode, optional user-facing notice, and settings-change flag.
 */
readonly class SlashCommandAgentPromptResult implements Arrayable
{
    /**
     * @param  string  $displayPrompt  Prompt text to display to the user.
     * @param  string  $prompt  Prompt text submitted to the agent.
     * @param  string|null  $mode  Optional target session mode for the agent prompt.
     * @param  string|null  $notice  Optional user-facing notice to show before the prompt is submitted.
     * @param  bool|null  $runtimeSettingsChanged  True when the invocation mutated user runtime settings; consumers caching settings should refresh.
     */
    public function __construct(
        public string $displayPrompt,
        public string $prompt,
        public ?string $mode = null,
        public ?string $notice = null,
        public ?bool $runtimeSettingsChanged = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            displayPrompt: Arr::string($data, 'displayPrompt'),
            prompt: Arr::string($data, 'prompt'),
            mode: $data['mode'] ?? null,
            notice: $data['notice'] ?? null,
            runtimeSettingsChanged: isset($data['runtimeSettingsChanged']) ? (bool) $data['runtimeSettingsChanged'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'displayPrompt' => $this->displayPrompt,
            'prompt' => $this->prompt,
            'mode' => $this->mode,
            'notice' => $this->notice,
            'runtimeSettingsChanged' => $this->runtimeSettingsChanged,
        ], fn ($value) => $value !== null);
    }
}
