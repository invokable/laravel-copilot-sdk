<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional launch profile returned by an extension launch provider.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ExtensionLaunchProviderResolveResult implements Arrayable
{
    public function __construct(
        public ?ExtensionLaunchProfile $launch = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $launch = $data['launch'] ?? null;

        return new self(
            launch: $launch instanceof ExtensionLaunchProfile
                ? $launch
                : (is_array($launch) ? ExtensionLaunchProfile::fromArray($launch) : null),
        );
    }

    public function toArray(): array
    {
        return $this->launch === null ? [] : ['launch' => $this->launch->toArray()];
    }
}
