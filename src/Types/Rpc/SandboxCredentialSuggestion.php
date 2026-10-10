<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A possible sandbox credential for user review; it is not an active grant.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxCredentialSuggestion implements Arrayable
{
    /**
     * @param  string  $name  Environment variable name; its secret value is never returned
     * @param  list<string>  $suggestedInjectHosts  Locally suggested HTTPS hosts for credential injection
     */
    public function __construct(
        public string $name,
        public array $suggestedInjectHosts,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            suggestedInjectHosts: Arr::array($data, 'suggestedInjectHosts', []),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'suggestedInjectHosts' => $this->suggestedInjectHosts,
        ];
    }
}
