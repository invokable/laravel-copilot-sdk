<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Capabilities reported by the CLI host for this session.
 */
readonly class SessionCapabilities implements Arrayable
{
    /**
     * @param  ?array{elicitation?: bool}  $ui  UI capabilities
     */
    public function __construct(
        public ?array $ui = null,
    ) {}

    /**
     * Whether the host supports interactive elicitation dialogs.
     */
    public function supportsElicitation(): bool
    {
        return (bool) ($this->ui['elicitation'] ?? false);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ui: $data['ui'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ui' => $this->ui,
        ], fn ($v) => $v !== null);
    }
}
