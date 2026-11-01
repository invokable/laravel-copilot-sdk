<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Runtime support and session-context standalone executable extension policy. */
readonly class ExtensionManagedPolicy implements Arrayable
{
    /**
     * @param  array<array{id: string, modulePath: string}>  $eligiblePluginEntries
     */
    public function __construct(
        public string $standalone,
        public int $enforcementVersion,
        public array $eligiblePluginEntries = [],
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            standalone: $data['standalone'] ?? 'pending',
            enforcementVersion: $data['enforcementVersion'] ?? 0,
            eligiblePluginEntries: $data['eligiblePluginEntries'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'standalone' => $this->standalone,
            'enforcementVersion' => $this->enforcementVersion,
            'eligiblePluginEntries' => $this->eligiblePluginEntries,
        ];
    }
}
