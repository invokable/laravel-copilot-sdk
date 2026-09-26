<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SandboxSessionChange;

/**
 * Text result returned by a slash command.
 *
 * @experimental
 */
readonly class SlashCommandTextResult implements Arrayable
{
    public function __construct(
        public string $text,
        public ?bool $markdown = null,
        public ?bool $preserveAnsi = null,
        public ?bool $runtimeSettingsChanged = null,
        public SandboxSessionChange|string|null $sandboxSessionChange = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $sandboxSessionChange = $data['sandboxSessionChange'] ?? null;

        return new self(
            text: $data['text'] ?? '',
            markdown: $data['markdown'] ?? null,
            preserveAnsi: $data['preserveAnsi'] ?? null,
            runtimeSettingsChanged: $data['runtimeSettingsChanged'] ?? null,
            sandboxSessionChange: is_string($sandboxSessionChange)
                ? (SandboxSessionChange::tryFrom($sandboxSessionChange) ?? $sandboxSessionChange)
                : $sandboxSessionChange,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => 'text',
            'text' => $this->text,
            'markdown' => $this->markdown,
            'preserveAnsi' => $this->preserveAnsi,
            'runtimeSettingsChanged' => $this->runtimeSettingsChanged,
            'sandboxSessionChange' => $this->sandboxSessionChange instanceof SandboxSessionChange
                ? $this->sandboxSessionChange->value
                : $this->sandboxSessionChange,
        ], fn ($value) => $value !== null);
    }
}
