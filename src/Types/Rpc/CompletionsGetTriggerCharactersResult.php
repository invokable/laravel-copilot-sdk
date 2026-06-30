<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Characters that, when typed in the composer, should trigger a completions.request.
 * Empty when the session has no host-driven completions.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CompletionsGetTriggerCharactersResult implements Arrayable
{
    /**
     * @param  string[]  $triggerCharacters  Trigger characters advertised by the host (e.g. ["@", "#"]). Empty disables host-driven completions for the session.
     */
    public function __construct(
        public array $triggerCharacters,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            triggerCharacters: Arr::array($data, 'triggerCharacters'),
        );
    }

    public function toArray(): array
    {
        return [
            'triggerCharacters' => $this->triggerCharacters,
        ];
    }
}
