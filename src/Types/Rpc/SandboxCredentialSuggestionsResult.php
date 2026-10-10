<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Possible unconfigured sandbox credentials, without secret values.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxCredentialSuggestionsResult implements Arrayable
{
    /**
     * @param  list<SandboxCredentialSuggestion>  $suggestions  Candidates for user review
     */
    public function __construct(
        public array $suggestions,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            suggestions: array_map(
                fn (array $suggestion) => SandboxCredentialSuggestion::fromArray($suggestion),
                Arr::array($data, 'suggestions', []),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'suggestions' => array_map(
                fn (SandboxCredentialSuggestion $suggestion) => $suggestion->toArray(),
                $this->suggestions,
            ),
        ];
    }
}
